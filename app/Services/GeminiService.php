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
Eres un ingeniero biomédico senior recibiendo un reporte escrito por personal no técnico del hospital.
El mensaje puede ser informal, confuso o un párrafo libre.

Servicio que reporta: "{$servicioReportante}"
Texto original: "{$textoLibre}"

Extrae ÚNICAMENTE lo que esté mencionado con claridad. Devuelve ÚNICAMENTE un JSON válido (sin markdown, sin texto extra):
{
  "nombre_dispositivo": "nombre o descripción del equipo médico si está claramente mencionado. Null si hay duda.",
  "marca": "marca del equipo si está explícitamente escrita. Null si no se menciona.",
  "modelo": "modelo del equipo si está explícitamente escrito. Null si no se menciona.",
  "numero_serie": "número de serie si está explícitamente escrito. Null si no se menciona.",
  "numero_control": "número de control o inventario si está explícitamente escrito. Null si no se menciona.",
  "tipo_servicio": "preventivo si solicita mantenimiento preventivo, correctivo si reporta falla o problema. Null si no es claro.",
  "tipo_baja": "una de: no_funcional, inservible, obsoleto, disposicion, traspaso, otro — solo si el texto indica que se solicita dar de baja el equipo. Null si no es una baja.",
  "ubicacion": "ubicación física si está mencionada. Null si no se menciona.",
  "prioridad": "una de: baja, media, moderada, urgencia"
}

Reglas estrictas:
- NUNCA inventes ni inferras datos que no estén escritos explícitamente (excepto prioridad y tipo_servicio/tipo_baja que puedes inferir por contexto)
- tipo_servicio y tipo_baja son mutuamente excluyentes: solo uno puede ser no-null
- Si no es claro si es servicio o baja, ambos null
- ubicacion null si no se menciona; no uses el servicio como ubicación

Criterio de prioridad:
- urgencia: equipo de soporte vital (ventilador, desfibrilador, monitor UCI), riesgo inmediato
- moderada: equipo importante sin alternativa inmediata
- media: equipo diagnóstico con alternativa disponible
- baja: equipo administrativo, falla menor, o mensaje vago
PROMPT;

        if (empty($this->apiKey)) {
            Log::warning('GeminiService: GEMINI_API_KEY no configurada');
            return $this->fallback($textoLibre, $servicioReportante);
        }

        try {
            $response = Http::timeout(30)->post("{$this->endpoint}?key={$this->apiKey}", [
                'contents'         => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => ['temperature' => 0.1, 'maxOutputTokens' => 512],
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

            $prioridad     = strtolower(trim($datos['prioridad'] ?? ''));
            $tipoServicio  = strtolower(trim($datos['tipo_servicio'] ?? ''));
            $tipoBaja      = strtolower(trim($datos['tipo_baja'] ?? ''));

            $validosTipoBaja = ['no_funcional','inservible','obsoleto','disposicion','traspaso','otro'];

            return [
                'nombre_dispositivo' => $this->nullableStr($datos['nombre_dispositivo'] ?? null),
                'marca'              => $this->nullableStr($datos['marca'] ?? null),
                'modelo'             => $this->nullableStr($datos['modelo'] ?? null),
                'numero_serie'       => $this->nullableStr($datos['numero_serie'] ?? null),
                'numero_control'     => $this->nullableStr($datos['numero_control'] ?? null),
                'tipo_servicio'      => in_array($tipoServicio, ['preventivo','correctivo']) ? $tipoServicio : null,
                'tipo_baja'          => in_array($tipoBaja, $validosTipoBaja) ? $tipoBaja : null,
                'ubicacion'          => $this->nullableStr($datos['ubicacion'] ?? null),
                'prioridad'          => in_array($prioridad, ['baja','media','moderada','urgencia']) ? $prioridad : 'baja',
            ];

        } catch (\Exception $e) {
            Log::error('GeminiService::extraerDatosReporte excepción', ['error' => $this->safeError($e)]);
            return $this->fallback($textoLibre, $servicioReportante);
        }
    }

    private function nullableStr(mixed $val): ?string
    {
        if ($val === null || strtolower(trim((string) $val)) === 'null' || trim((string) $val) === '') {
            return null;
        }
        return trim((string) $val);
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
            Log::error('GeminiService::sugerirBitacora excepción', ['error' => $this->safeError($e)]);
            return array_merge($base, [
                '_fuente' => 'fallback',
                '_aviso'  => 'Error de conexión con la IA: ' . $this->safeError($e) . '. Se rellenaron los campos básicos.',
            ]);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────────────────────

    private function safeError(\Throwable $e): string
    {
        if (empty($this->apiKey)) {
            return $e->getMessage();
        }
        return str_replace($this->apiKey, '[REDACTED]', $e->getMessage());
    }

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

    // ──────────────────────────────────────────────────────────────
    // Método 3: sugiere campos para un formulario de ConcretarVale
    // ──────────────────────────────────────────────────────────────

    /**
     * Pre-rellena el formulario de concretar vale usando IA.
     * Siempre devuelve datos (nunca lanza excepción).
     * Si la API no está disponible o falla, devuelve los datos existentes del vale.
     */
    public function sugerirVale(\App\Models\ValeInventario $vale): array
    {
        $base = $this->baseParaVale($vale);

        if (empty($this->apiKey)) {
            return array_merge($base, [
                '_fuente' => 'fallback',
                '_aviso'  => 'La clave de la API de IA no está configurada. Se usaron los datos del inventario.',
            ]);
        }

        $areasLista  = implode(', ', self::AREAS);
        $tipo        = $vale->tipo === 'retiro' ? 'Retiro (baja)' : 'Entrega (alta)';
        $equipo      = $vale->equipo_nombre ?? '';
        $area        = $vale->area ?? '';
        $marca       = $vale->marca ?? '';
        $modelo      = $vale->modelo ?? '';
        $serie       = $vale->numero_serie ?? '';
        $inventario  = $vale->numero_inventario ?? '';

        $prompt = <<<PROMPT
Eres un ingeniero biomédico completando la documentación de una transferencia de equipo médico.

Datos del vale de inventario:
- Tipo: {$tipo}
- Equipo: "{$equipo}"
- Área/Servicio actual: "{$area}"
- Marca: "{$marca}"
- Modelo: "{$modelo}"
- No. Serie: "{$serie}"
- No. Inventario: "{$inventario}"

Áreas válidas del hospital (usa EXACTAMENTE una de estas o null):
{$areasLista}

Devuelve ÚNICAMENTE un JSON válido (sin markdown, sin texto extra):
{
  "equipo_nombre": "nombre técnico normalizado del equipo. Si ya es correcto, devuélvelo igual. Mejóralo si está incompleto.",
  "area": "la opción de la lista que mejor corresponde al área indicada, null si ninguna coincide claramente",
  "marca": "marca del equipo si está disponible o puede inferirse, null si no hay certeza",
  "modelo": "modelo del equipo si está disponible, null si no hay certeza",
  "observaciones": "una observación técnica breve relevante para este tipo de vale (máx. 1 oración)"
}

Reglas:
- Solo mejora o normaliza lo que ya existe — no inventes datos nuevos
- Para el área, busca la coincidencia exacta en la lista proporcionada
- Las observaciones deben ser concisas y profesionales
PROMPT;

        try {
            $response = Http::timeout(25)->post("{$this->endpoint}?key={$this->apiKey}", [
                'contents'         => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => ['temperature' => 0.1, 'maxOutputTokens' => 512],
            ]);

            if (! $response->successful()) {
                return array_merge($base, ['_fuente' => 'fallback', '_aviso' => null]);
            }

            $texto = trim($response->json('candidates.0.content.parts.0.text', ''));

            // Extraer primer objeto JSON aunque haya texto o markdown alrededor
            if (! preg_match('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)?\}/us', $texto, $matches)) {
                // Silently fall back — el formulario ya tiene datos del vale
                return array_merge($base, ['_fuente' => 'fallback', '_aviso' => null]);
            }

            $datos = json_decode($matches[0], true);
            if (! is_array($datos)) {
                return array_merge($base, ['_fuente' => 'fallback', '_aviso' => null]);
            }

            $area = $datos['area'] ?? null;
            if ($area && ! in_array($area, self::AREAS)) {
                $area = $this->matchearArea($area) ?? $base['area'];
            }

            return array_merge($base, array_filter([
                'equipo_nombre'  => ! empty($datos['equipo_nombre']) ? $datos['equipo_nombre'] : $base['equipo_nombre'],
                'area'           => $area ?? $base['area'],
                'marca'          => ! empty($datos['marca']) && strtolower($datos['marca']) !== 'null' ? $datos['marca'] : $base['marca'],
                'modelo'         => ! empty($datos['modelo']) && strtolower($datos['modelo']) !== 'null' ? $datos['modelo'] : $base['modelo'],
                'observaciones'  => ! empty($datos['observaciones']) ? $datos['observaciones'] : $base['observaciones'],
            ], fn ($v) => $v !== null), [
                '_fuente' => 'ia',
                '_aviso'  => null,
            ]);

        } catch (\Exception $e) {
            Log::error('GeminiService::sugerirVale excepción', ['error' => $this->safeError($e)]);
            return array_merge($base, ['_fuente' => 'fallback', '_aviso' => null]);
        }
    }

    private function baseParaVale(\App\Models\ValeInventario $vale): array
    {
        return [
            'tipo'            => $vale->tipo,
            'equipo_nombre'   => $vale->equipo_nombre,
            'numero_inventario' => $vale->numero_inventario,
            'area'            => $this->matchearArea($vale->area ?? '') ?? $vale->area,
            'marca'           => $vale->marca,
            'modelo'          => $vale->modelo,
            'numero_serie'    => $vale->numero_serie,
            'unidad_medica'   => $vale->unidad_medica,
            'usuario_nombre'  => $vale->usuario_nombre,
            'quien_recibe'    => $vale->quien_recibe,
            'cargo_recibe'    => $vale->cargo_recibe,
            'observaciones'   => $vale->observaciones,
        ];
    }

    /**
     * Fallback para extraerDatosReporte cuando la IA no está disponible.
     */
    private function fallback(string $textoLibre, string $servicio): array
    {
        return [
            'nombre_dispositivo' => null,
            'marca'              => null,
            'modelo'             => null,
            'numero_serie'       => null,
            'numero_control'     => null,
            'tipo_servicio'      => null,
            'tipo_baja'          => null,
            'ubicacion'          => $servicio,
            'prioridad'          => 'baja',
        ];
    }
}
