<?php

namespace App\Services;

use App\Models\ReportePizarron;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    // Opciones válidas de área (espejo de BitacoraReporteResource)
    private const AREAS = [
        'Audiología','Anestesiología','Banco de Sangre','Banco de Leches',
        'Cardiología','CEYE','Cirugía Ambulatoria','Cirugías','Clínica de catéter',
        'Clínica displacías','Consultorio pediatría','Consultorio ginecología',
        'Crecimiento y desarrollo','Cuidados intermedios','Dermatología','Dietología',
        'Endoscopia','Farmacia','Ginecología y obstetricia','Hemodiálisis','Hemodinamia',
        'Imagenología','Inhaloterapia','Laboratorio','Lactantes','Maxilofacial',
        'Neonatología','Medicina interna','Neurología','Oncología adultos',
        'Oncología pediátrica','Oftalmología','Ortopedia','Otorrinolaringología',
        'Patología','Pediatría','Quemados','Quirófano','Radioterapia','Rehabilitación',
        'Reumatología','Somatometría','Tococirugía','Trasplantes',
        'UCIA','UCIN','UCIN aislados','UCIP','Urgencias',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', '');
    }

    // ──────────────────────────────────────────────────────────────
    // Método 1: extrae datos del reporte libre para el pizarrón
    // ──────────────────────────────────────────────────────────────
    public function extraerDatosReporte(string $textoLibre, string $servicioReportante): array
    {
        $prompt = <<<PROMPT
Eres un ingeniero biomédico senior recibiendo un reporte de falla de equipo médico.
El reporte fue escrito por personal no técnico del hospital y puede ser confuso, largo o informal.

Servicio que reporta: "{$servicioReportante}"
Texto original: "{$textoLibre}"

Tu tarea es extraer ÚNICAMENTE la información que esté claramente mencionada en el texto. Devuelve ÚNICAMENTE un JSON válido con esta estructura exacta (sin markdown, sin texto adicional, solo el JSON):
{
  "equipo": "nombre del equipo médico solo si está explícitamente mencionado o se puede identificar con total certeza. Null si hay cualquier duda.",
  "ubicacion": "ubicación solo si está mencionada en el texto. Null si no se menciona.",
  "descripcion": "copia exacta del texto original sin ninguna modificación, exactamente como fue recibido.",
  "prioridad": "una de estas cuatro opciones exactas: baja, media, moderada, urgencia"
}

Reglas estrictas:
- NUNCA inventes, supongas ni inferras información que no esté escrita explícitamente
- Si el equipo no se menciona con claridad, pon null. No adivines por el servicio o el contexto
- Si la ubicación no se menciona, pon null. No uses el servicio como ubicación
- El campo descripcion SIEMPRE debe ser el texto original exacto, sin ninguna modificación

Criterio de prioridad (único campo donde puedes inferir por contexto):
- urgencia: equipo de soporte vital (ventilador, desfibrilador, monitor UCI), riesgo inmediato para el paciente
- moderada: equipo importante para el diagnóstico o tratamiento, sin alternativa inmediata disponible
- media: equipo diagnóstico o de monitoreo con alternativa disponible
- baja: equipo administrativo, de confort, falla menor, o mensaje muy vago sin información suficiente
PROMPT;

        if (empty($this->apiKey)) {
            Log::warning('GeminiService: GEMINI_API_KEY no configurada');
            return $this->fallback($textoLibre, $servicioReportante);
        }

        try {
            $response = Http::timeout(30)->post("{$this->endpoint}?key={$this->apiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => ['temperature' => 0.1, 'maxOutputTokens' => 4096],
            ]);

            if (! $response->successful()) {
                Log::warning('GeminiService: respuesta no exitosa', ['status' => $response->status()]);
                return $this->fallback($textoLibre, $servicioReportante);
            }

            $texto = trim($response->json('candidates.0.content.parts.0.text', ''));

            if (! preg_match('/\{[\s\S]*\}/u', $texto, $matches)) {
                Log::warning('GeminiService: no se encontró JSON en la respuesta');
                return $this->fallback($textoLibre, $servicioReportante);
            }

            $datos = json_decode($matches[0], true);
            if (! is_array($datos)) {
                return $this->fallback($textoLibre, $servicioReportante);
            }

            $prioridad = strtolower(trim($datos['prioridad'] ?? ''));

            return [
                'equipo'      => ! empty($datos['equipo']) && strtolower($datos['equipo']) !== 'null'
                                    ? $datos['equipo'] : null,
                'ubicacion'   => ! empty($datos['ubicacion']) ? $datos['ubicacion'] : $servicioReportante,
                'descripcion' => ! empty($datos['descripcion']) ? $datos['descripcion'] : $textoLibre,
                'prioridad'   => in_array($prioridad, ['baja','media','moderada','urgencia'])
                                    ? $prioridad : 'baja',
            ];

        } catch (\Exception $e) {
            Log::error('GeminiService::extraerDatosReporte excepción', ['error' => $e->getMessage()]);
            return $this->fallback($textoLibre, $servicioReportante);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // Método 2: sugiere campos para una BitacoraReporte
    // ──────────────────────────────────────────────────────────────

    /**
     * Retorna array con campos listos para pre-llenar el formulario de BitacoraReporte.
     * Siempre devuelve datos (nunca lanza excepción).
     * El campo '_fuente' indica 'ia' o 'fallback'.
     * El campo '_aviso' lleva un mensaje humano si algo falló.
     */
    public function sugerirBitacora(ReportePizarron $reporte): array
    {
        $base = $this->baseParaBitacora($reporte);

        // Si no hay API key, devolver sólo datos base
        if (empty($this->apiKey)) {
            return array_merge($base, [
                '_fuente' => 'fallback',
                '_aviso'  => 'La clave de la API de IA no está configurada. Se rellenaron los campos básicos del reporte.',
            ]);
        }

        $areasLista = implode(', ', self::AREAS);
        $mensaje    = $reporte->descripcion_original ?? $reporte->descripcion ?? '';
        $equipo     = $reporte->equipo ?? '';
        $servicio   = $reporte->reportante_servicio ?? $reporte->ubicacion ?? '';
        $responsable = $reporte->responsable ?? '';

        $prompt = <<<PROMPT
Eres un ingeniero biomédico preparando la documentación técnica de una intervención.

Datos del reporte recibido:
- Mensaje del usuario: "{$mensaje}"
- Equipo reportado: "{$equipo}"
- Servicio/área que reporta: "{$servicio}"
- Ingeniero responsable: "{$responsable}"

Áreas válidas del hospital (usa EXACTAMENTE una de estas o null):
{$areasLista}

Devuelve ÚNICAMENTE un JSON válido (sin markdown, sin texto extra):
{
  "nombre_dispositivo": "nombre técnico normalizado del equipo. Si el equipo ya está indicado, úsalo tal cual o mejóralo levemente. Si no hay equipo claro, escribe 'Por determinar'.",
  "marca": "marca del equipo si se menciona explícitamente en el mensaje, null si no",
  "modelo": "modelo del equipo si se menciona explícitamente en el mensaje, null si no",
  "area_departamento": "la opción de la lista que mejor corresponde al servicio que reporta, null si ninguna coincide",
  "acciones": [
    {"texto": "primera acción técnica de diagnóstico o intervención"},
    {"texto": "segunda acción técnica"}
  ]
}

Reglas para las acciones:
- Sugiere 2 o 3 pasos técnicos concretos de diagnóstico/intervención basados en el problema descrito
- Si el mensaje es muy vago, sugiere pasos generales: inspección visual, prueba de funcionamiento, revisión de conexiones
- Escríbelas en primera persona del plural (Ej: "Se realizó inspección visual del equipo...")
- Máximo 3 acciones
PROMPT;

        try {
            $response = Http::timeout(30)->post("{$this->endpoint}?key={$this->apiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 1024],
            ]);

            if (! $response->successful()) {
                Log::warning('GeminiService::sugerirBitacora: respuesta no exitosa', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return array_merge($base, [
                    '_fuente' => 'fallback',
                    '_aviso'  => 'La IA no respondió correctamente (error ' . $response->status() . '). Se rellenaron los campos básicos.',
                ]);
            }

            $texto = trim($response->json('candidates.0.content.parts.0.text', ''));

            if (! preg_match('/\{[\s\S]*\}/u', $texto, $matches)) {
                Log::warning('GeminiService::sugerirBitacora: sin JSON en respuesta', ['raw' => $texto]);
                return array_merge($base, [
                    '_fuente' => 'fallback',
                    '_aviso'  => 'La IA devolvió una respuesta inesperada. Se rellenaron los campos básicos.',
                ]);
            }

            $datos = json_decode($matches[0], true);
            if (! is_array($datos)) {
                return array_merge($base, [
                    '_fuente' => 'fallback',
                    '_aviso'  => 'No se pudo interpretar la respuesta de la IA. Se rellenaron los campos básicos.',
                ]);
            }

            // Normalizar acciones: asegurar formato correcto para el Repeater
            $acciones = [];
            foreach ($datos['acciones'] ?? [] as $accion) {
                $texto = is_array($accion) ? ($accion['texto'] ?? '') : (string) $accion;
                if (! empty(trim($texto))) {
                    $acciones[] = ['texto' => trim($texto), 'imagen_path' => null, 'pie_imagen' => ''];
                }
            }
            if (empty($acciones)) {
                $acciones = [['texto' => '', 'imagen_path' => null, 'pie_imagen' => '']];
            }

            // Validar area_departamento contra la lista
            $area = $datos['area_departamento'] ?? null;
            if ($area && ! in_array($area, self::AREAS)) {
                $area = $this->matchearArea($area) ?? $base['area_departamento'];
            }

            return array_merge($base, [
                'nombre_dispositivo' => ! empty($datos['nombre_dispositivo']) ? $datos['nombre_dispositivo'] : $base['nombre_dispositivo'],
                'marca'              => ! empty($datos['marca']) && strtolower($datos['marca']) !== 'null' ? $datos['marca'] : null,
                'modelo'             => ! empty($datos['modelo']) && strtolower($datos['modelo']) !== 'null' ? $datos['modelo'] : null,
                'area_departamento'  => $area ?? $base['area_departamento'],
                'acciones'           => $acciones,
                '_fuente'            => 'ia',
                '_aviso'             => null,
            ]);

        } catch (\Exception $e) {
            Log::error('GeminiService::sugerirBitacora excepción', ['error' => $e->getMessage()]);
            return array_merge($base, [
                '_fuente' => 'fallback',
                '_aviso'  => 'Error de conexión con la IA: ' . $e->getMessage() . '. Se rellenaron los campos básicos.',
            ]);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────────────────────

    /**
     * Datos base siempre disponibles sin IA.
     */
    private function baseParaBitacora(ReportePizarron $reporte): array
    {
        return [
            'nombre_personal'    => $reporte->reportante_nombre ?? null,
            'area_departamento'  => $this->matchearArea($reporte->reportante_servicio ?? '') ?? null,
            'mensaje_original'   => $reporte->descripcion_original ?? $reporte->descripcion ?? null,
            'fecha_reporte'      => now()->toDateString(),
            'hora_reporte'       => now()->format('H:i'),
            'nombre_dispositivo' => $reporte->equipo ?? null,
            'atiende_nombre'     => $reporte->responsable ?? null,
            'acciones'           => [['texto' => '', 'imagen_path' => null, 'pie_imagen' => '']],
            'marca'              => null,
            'modelo'             => null,
        ];
    }

    /**
     * Busca la opción de área más cercana al texto dado.
     */
    private function matchearArea(?string $texto): ?string
    {
        if (empty($texto)) return null;

        $lower = strtolower(trim($texto));

        // Coincidencia exacta
        foreach (self::AREAS as $area) {
            if (strtolower($area) === $lower) return $area;
        }

        // Coincidencia parcial (el texto contiene el área o viceversa)
        foreach (self::AREAS as $area) {
            if (str_contains($lower, strtolower($area)) || str_contains(strtolower($area), $lower)) {
                return $area;
            }
        }

        return null;
    }

    /**
     * Fallback para extraerDatosReporte cuando la IA no está disponible.
     */
    private function fallback(string $textoLibre, string $servicio): array
    {
        return [
            'equipo'      => null,
            'ubicacion'   => $servicio,
            'descripcion' => $textoLibre,
            'prioridad'   => 'baja',
        ];
    }
}
