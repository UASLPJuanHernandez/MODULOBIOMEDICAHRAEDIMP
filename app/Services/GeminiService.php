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

Tu tarea es extraer y estructurar la información. Devuelve ÚNICAMENTE un JSON válido con esta estructura exacta (sin markdown, sin texto adicional, solo el JSON):
{
  "equipo": "nombre técnico del equipo médico (ej: ventilador mecánico, monitor de signos vitales, bomba de infusión). Null si no se puede identificar.",
  "ubicacion": "ubicación precisa: incluye área, cuarto, cama o número de consultorio. Incluye el servicio si aporta contexto.",
  "descripcion": "descripción técnica concisa de máximo 2 oraciones. Reescribe el texto original: elimina relleno, informalidades y repeticiones. Menciona el síntoma principal y cuándo ocurre si se sabe.",
  "prioridad": "una de estas cuatro opciones exactas: baja, media, moderada, urgencia"
}

Criterio de prioridad:
- urgencia: equipo de soporte vital (ventilador, desfibrilador, monitor UCI), riesgo inmediato para el paciente
- moderada: equipo importante para el diagnóstico o tratamiento, sin alternativa inmediata disponible
- media: equipo diagnóstico o de monitoreo con alternativa disponible
- baja: equipo administrativo, de confort, o falla menor sin impacto en atención

Reglas para la descripcion:
- Máximo 2 oraciones claras y directas
- Usa lenguaje técnico-hospitalario, no coloquial
- Incluye: qué falla, cómo falla, desde cuándo si se menciona
- Si el texto es ambiguo, describe lo que se pueda inferir con certeza
PROMPT;

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
