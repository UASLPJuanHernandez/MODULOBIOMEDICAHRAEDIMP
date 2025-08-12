<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Proveedor;
use App\Models\TipoPartida;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipoPartida = TipoPartida::first();
        
        $proveedores = [
            [
                'partida_id' => $tipoPartida->id,
                'nombre_proveedor' => 'Medtronic México',
                'monto_unitario' => 150000.00,
                'monto_total' => 300000.00,
                'cantidad_mobiliario' => 2,
            ],
            [
                'partida_id' => $tipoPartida->id,
                'nombre_proveedor' => 'GE Healthcare',
                'monto_unitario' => 250000.00,
                'monto_total' => 500000.00,
                'cantidad_mobiliario' => 2,
            ],
            [
                'partida_id' => $tipoPartida->id,
                'nombre_proveedor' => 'Philips Healthcare',
                'monto_unitario' => 180000.00,
                'monto_total' => 360000.00,
                'cantidad_mobiliario' => 2,
            ],
            [
                'partida_id' => $tipoPartida->id,
                'nombre_proveedor' => 'Siemens Healthineers',
                'monto_unitario' => 200000.00,
                'monto_total' => 400000.00,
                'cantidad_mobiliario' => 2,
            ],
            [
                'partida_id' => $tipoPartida->id,
                'nombre_proveedor' => 'Mindray Medical',
                'monto_unitario' => 75000.00,
                'monto_total' => 225000.00,
                'cantidad_mobiliario' => 3,
            ],
            [
                'partida_id' => $tipoPartida->id,
                'nombre_proveedor' => 'Hill-Rom Holdings',
                'monto_unitario' => 45000.00,
                'monto_total' => 180000.00,
                'cantidad_mobiliario' => 4,
            ],
            [
                'partida_id' => $tipoPartida->id,
                'nombre_proveedor' => 'Steris Corporation',
                'monto_unitario' => 35000.00,
                'monto_total' => 105000.00,
                'cantidad_mobiliario' => 3,
            ],
            [
                'partida_id' => $tipoPartida->id,
                'nombre_proveedor' => 'Stryker Corporation',
                'monto_unitario' => 120000.00,
                'monto_total' => 240000.00,
                'cantidad_mobiliario' => 2,
            ],
            [
                'partida_id' => $tipoPartida->id,
                'nombre_proveedor' => 'Cardinal Health',
                'monto_unitario' => 25000.00,
                'monto_total' => 100000.00,
                'cantidad_mobiliario' => 4,
            ],
            [
                'partida_id' => $tipoPartida->id,
                'nombre_proveedor' => 'Becton Dickinson',
                'monto_unitario' => 15000.00,
                'monto_total' => 60000.00,
                'cantidad_mobiliario' => 4,
            ],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }
    }
}
