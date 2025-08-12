<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoPartida;

class TipoPartidaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['tipo_partida' => 'por fecha'],
            ['tipo_partida' => 'por documento'],
            ['tipo_partida' => 'por tipo de transacción'],
        ];

        foreach ($tipos as $tipo) {
            TipoPartida::create($tipo);
        }
    }
}
