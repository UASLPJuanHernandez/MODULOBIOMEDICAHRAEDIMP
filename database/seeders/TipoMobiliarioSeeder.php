<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoMobiliario;

class TipoMobiliarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'tipo' => 'Equipo Médico',
                'categoria' => 'Servicios de Salud',
                'prefijo' => 'F'
            ],
            [
                'tipo' => 'Equipo no Médico',
                'categoria' => 'Servicios de Salud',
                'prefijo' => 'F'
            ],
            [
                'tipo' => 'Equipo Médico',
                'categoria' => 'Comodato',
                'prefijo' => 'E'
            ],
            [
                'tipo' => 'Equipo no Médico',
                'categoria' => 'Comodato',
                'prefijo' => 'E'
            ],
            [
                'tipo' => 'Equipo Médico',
                'categoria' => 'Servicios Integrales',
                'prefijo' => 'SI'
            ],
            [
                'tipo' => 'Equipo no Médico',
                'categoria' => 'Servicios Integrales',
                'prefijo' => 'SI'
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoMobiliario::create($tipo);
        }
    }
}
