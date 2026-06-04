<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class AuditService
{
    /**
     * Registra un evento de auditoría. Nunca lanza excepción para no interrumpir el flujo principal.
     *
     * Tipos comunes: 'acceso', 'firma', 'usuario'
     * Actor tipos:   'personal', 'admin', 'sistema'
     * Documento tipos: 'vale', 'solicitud', 'registro', 'bitacora'
     */
    public static function log(string $tipo, string $descripcion, array $opts = []): void
    {
        try {
            AuditLog::create([
                'tipo'           => $tipo,
                'descripcion'    => $descripcion,
                'actor_tipo'     => $opts['actor_tipo']     ?? 'sistema',
                'actor_id'       => $opts['actor_id']       ?? 0,
                'actor_nombre'   => $opts['actor_nombre']   ?? 'Sistema',
                'documento_tipo' => $opts['documento_tipo'] ?? null,
                'documento_id'   => $opts['documento_id']   ?? null,
                'ip'             => request()?->ip(),
                'metadata'       => array_filter(array_merge(
                                       $opts['metadata'] ?? [],
                                       isset($opts['origen']) ? ['origen' => $opts['origen']] : []
                                   )) ?: null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('AuditService::log falló: ' . $e->getMessage());
        }
    }
}
