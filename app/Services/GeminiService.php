<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    /**
     * Extrae datos estructurados del texto libre de un reporte de falla.
     * Devuelve array con: equipo, ubicacion, descripcion, prioridad
     */
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
            Log::error('GeminiService: GEMINI_API_KEY no está configurada en .env');
            return $this->fallback($textoLibre, $servicioReportante);
        }

        try {
            $response = Http::timeout(30)->post("{$this->endpoint}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature'     => 0.1,
                    'maxOutputTokens' => 4096,
                ],
            ]);

            if (! $response->successful()) {
                Log::warning('GeminiService: respuesta no exitosa', ['status' => $response->status()]);
                return $this->fallback($textoLibre, $servicioReportante);
            }

            $texto = $response->json('candidates.0.content.parts.0.text', '');
            $texto = trim($texto);

            // Extraer el primer objeto JSON que aparezca, sin importar
            // si Gemini lo envolvió en markdown u otros textos
            if (! preg_match('/\{[\s\S]*\}/u', $texto, $matches)) {
                Log::warning('GeminiService: no se encontró JSON en la respuesta', ['raw' => $texto]);
                return $this->fallback($textoLibre, $servicioReportante);
            }

            $datos = json_decode($matches[0], true);

            if (! is_array($datos)) {
                Log::warning('GeminiService: JSON inválido', ['raw' => $matches[0]]);
                return $this->fallback($textoLibre, $servicioReportante);
            }

            $prioridad = strtolower(trim($datos['prioridad'] ?? ''));
            $prioridades = ['baja', 'media', 'moderada', 'urgencia'];

            return [
                'equipo'      => ! empty($datos['equipo']) && strtolower($datos['equipo']) !== 'null'
                                    ? $datos['equipo']
                                    : null,
                'ubicacion'   => ! empty($datos['ubicacion']) ? $datos['ubicacion'] : $servicioReportante,
                'descripcion' => ! empty($datos['descripcion']) ? $datos['descripcion'] : $textoLibre,
                'prioridad'   => in_array($prioridad, $prioridades) ? $prioridad : 'baja',
            ];

        } catch (\Exception $e) {
            Log::error('GeminiService: excepción', ['error' => $e->getMessage()]);
            return $this->fallback($textoLibre, $servicioReportante);
        }
    }

    /**
     * Valores de respaldo si la IA falla, para no bloquear el reporte.
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
