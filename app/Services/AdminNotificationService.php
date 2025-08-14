<?php

namespace App\Services;

use App\Events\AdminNotificationEvent;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdminNotificationService
{
    /**
     * Envía una notificación al administrador
     */
    public static function notify(string $title, string $message, string $action, User $user, array $data = []): void
    {
        Log::info("AdminNotificationService::notify llamado - Usuario: {$user->name} (ID: {$user->id})");
        Log::info("¿Es administrador? " . ($user->hasRole('Administrador') ? 'Sí' : 'No'));
        
        // Solo enviar si el usuario no es administrador
        if (!$user->hasRole('Administrador')) {
            Log::info('Enviando notificación en tiempo real porque el usuario NO es administrador');
            
            // Solo broadcast del evento en tiempo real con Laravel Reverb
            broadcast(new AdminNotificationEvent($title, $message, $action, $user, $data));
            Log::info('Evento broadcast enviado via Laravel Reverb');
        } else {
            Log::info('NO se envía notificación porque el usuario ES administrador');
        }
    }

    /**
     * Notificación para modificación de mobiliario
     */
    public static function mobiliarioModified(User $user, $mobiliario, string $action = 'modificado'): void
    {
        $title = 'Mobiliario ' . ucfirst($action);
        $message = "El usuario {$user->name} ha {$action} el mobiliario: {$mobiliario->numero_control} - {$mobiliario->descripcion}";
        
        self::notify($title, $message, 'mobiliario.' . $action, $user, [
            'mobiliario_id' => $mobiliario->id,
            'numero_control' => $mobiliario->numero_control,
        ]);
    }

    /**
     * Notificación para movimientos de área
     */
    public static function movimientoCreated(User $user, $movimiento): void
    {
        $title = 'Nuevo Movimiento de Área';
        $firstMobiliario = $movimiento->mobiliarios->first();
        $numeroControl = $firstMobiliario ? $firstMobiliario->numero_control : 'múltiples';
        $message = "El usuario {$user->name} ha realizado un movimiento de área para el mobiliario: {$numeroControl}";
        
        self::notify($title, $message, 'movimiento.created', $user, [
            'movimiento_id' => $movimiento->id,
        ]);
    }

    /**
     * Notificación para vales generados
     */
    public static function valeCreated(User $user, $vale): void
    {
        $title = 'Nuevo Vale Generado';
        $firstMobiliario = $vale->mobiliarios->first();
        $numeroControl = $firstMobiliario ? $firstMobiliario->numero_control : 'múltiples';
        $message = "El usuario {$user->name} ha generado un vale de {$vale->tipo_vale} para el mobiliario: {$numeroControl}";
        
        self::notify($title, $message, 'vale.created', $user, [
            'vale_id' => $vale->id,
            'tipo_vale' => $vale->tipo_vale,
        ]);
    }

    /**
     * Notificación para nueva localización
     */
    public static function localizacionCreated(User $user, $localizacion): void
    {
        $title = 'Nueva Localización Creada';
        $message = "El usuario {$user->name} ha creado una nueva localización: {$localizacion->ubicacion_resumida}";
        
        self::notify($title, $message, 'localizacion.created', $user, [
            'localizacion_id' => $localizacion->id,
        ]);
    }

    /**
     * Notificación para nueva clasificación de bien
     */
    public static function clasificacionBienCreated(User $user, $clasificacion): void
    {
        $title = 'Nueva Clasificación de Bien';
        $message = "El usuario {$user->name} ha creado una nueva clasificación: Grupo {$clasificacion->grupo} - {$clasificacion->nombre_grupo}";
        
        self::notify($title, $message, 'clasificacion.created', $user, [
            'clasificacion_id' => $clasificacion->id,
        ]);
    }

    /**
     * Notificación para equipo dado de baja
     */
    public static function equipoBaja(User $user, $mobiliario): void
    {
        $title = 'Equipo Dado de Baja';
        $message = "El usuario {$user->name} ha dado de baja el equipo: {$mobiliario->numero_control} - {$mobiliario->descripcion}";
        
        self::notify($title, $message, 'mobiliario.baja', $user, [
            'mobiliario_id' => $mobiliario->id,
            'numero_control' => $mobiliario->numero_control,
        ]);
    }

    /**
     * Notificación para generación de reportes
     */
    public static function reporteGenerado(User $user, string $tipoReporte): void
    {
        $title = 'Reporte Generado';
        $message = "El usuario {$user->name} ha generado un reporte de tipo: {$tipoReporte}";
        
        self::notify($title, $message, 'reporte.generated', $user, [
            'tipo_reporte' => $tipoReporte,
        ]);
    }
}
