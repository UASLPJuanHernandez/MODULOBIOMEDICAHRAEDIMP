<?php

namespace Database\Seeders;

use App\Models\ReportePizarron;
use Illuminate\Database\Seeder;

class ReportePizarronSeeder extends Seeder
{
    public function run(): void
    {
        $reportes = [
            [
                'titulo'      => 'Falla en ventilador UCI',
                'equipo'      => 'Ventilador Mecánico MAQUET SERVO-i',
                'ubicacion'   => 'UCI - Cama 3',
                'descripcion' => 'El ventilador presenta alarma continua de alta presión y no mantiene el volumen tidal programado. Paciente trasladado a ventilador de respaldo.',
                'prioridad'   => 'urgencia',
                'estado'      => 'pendiente',
                'minimizado'  => true,
            ],
            [
                'titulo'      => 'Monitor sin señal ECG',
                'equipo'      => 'Monitor de Signos Vitales Philips MX450',
                'ubicacion'   => 'Urgencias - Cubículo 7',
                'descripcion' => 'El monitor no detecta señal de ECG al conectar los electrodos. Las demás señales (SpO2, PA) funcionan correctamente. Cable de ECG ya fue descartado como causa.',
                'prioridad'   => 'moderada',
                'estado'      => 'en_curso',
                'responsable' => 'Ing. Daniela',
                'minimizado'  => false,
            ],
            [
                'titulo'      => 'Bomba de infusión con alarma',
                'equipo'      => 'Bomba de Infusión Baxter Sigma Spectrum',
                'ubicacion'   => 'Oncología - Hab. 214',
                'descripcion' => 'La bomba presenta alarma de oclusión intermitente sin causa aparente. Ya se revisó el set de infusión y se descartó bloqueo en línea. El problema persiste al reiniciar.',
                'prioridad'   => 'media',
                'estado'      => 'pendiente',
                'minimizado'  => true,
            ],
            [
                'titulo'      => 'Desfibrilador no enciende',
                'equipo'      => 'Desfibrilador ZOLL R Series',
                'ubicacion'   => 'Urgencias - Cuarto de Choque',
                'descripcion' => 'El equipo no responde al encendido con batería ni conectado a corriente. Fue reportado después de su uso en una emergencia la noche anterior.',
                'prioridad'   => 'urgencia',
                'estado'      => 'en_curso',
                'responsable' => 'Ing. Pedro',
                'minimizado'  => false,
            ],
            [
                'titulo'      => 'Electrocardiógrafo descalibrado',
                'equipo'      => 'Electrocardiógrafo MAC 5500 GE',
                'ubicacion'   => 'Consulta Externa - Consultorio 3',
                'descripcion' => 'El equipo imprime trazos con artefactos y la calibración automática falla. Se sospecha problema en el módulo de adquisición.',
                'prioridad'   => 'baja',
                'estado'      => 'pendiente',
                'minimizado'  => true,
            ],
            [
                'titulo'      => 'Cuna de calor con falla térmica',
                'equipo'      => 'Cuna de Calor Radiante Ohmeda Warmer',
                'ubicacion'   => 'UCIN - Posición 5',
                'descripcion' => 'La cuna no mantiene la temperatura programada, oscila entre 34°C y 38°C sin control estable. Sensor de piel verificado y en buen estado.',
                'prioridad'   => 'moderada',
                'estado'      => 'pendiente',
                'minimizado'  => true,
            ],
        ];

        foreach ($reportes as $reporte) {
            ReportePizarron::create($reporte);
        }
    }
}
