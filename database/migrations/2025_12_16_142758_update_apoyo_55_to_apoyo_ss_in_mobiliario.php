<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero modificar la columna enum para incluir ambos valores
        DB::statement("ALTER TABLE mobiliario MODIFY metodo_adquisicion ENUM('Compra', 'Donación', 'Apoyo 55', 'Apoyo SS', 'Comodato', 'Prestamo', 'Propiedad UASLP', 'IMSS-Bienestar', 'Equipo Personal', 'Otros')");
        
        // Actualizar todos los registros que tienen 'Apoyo 55' a 'Apoyo SS'
        DB::table('mobiliario')
            ->where('metodo_adquisicion', 'Apoyo 55')
            ->update(['metodo_adquisicion' => 'Apoyo SS']);
            
        // Finalmente, remover 'Apoyo 55' del enum
        DB::statement("ALTER TABLE mobiliario MODIFY metodo_adquisicion ENUM('Compra', 'Donación', 'Apoyo SS', 'Comodato', 'Prestamo', 'Propiedad UASLP', 'IMSS-Bienestar', 'Equipo Personal', 'Otros')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los registros de 'Apoyo SS' a 'Apoyo 55'
        DB::table('mobiliario')
            ->where('metodo_adquisicion', 'Apoyo SS')
            ->update(['metodo_adquisicion' => 'Apoyo 55']);
            
        // Revertir la columna enum al estado anterior
        DB::statement("ALTER TABLE mobiliario MODIFY metodo_adquisicion ENUM('Compra', 'Donación', 'Apoyo 55', 'Comodato', 'Prestamo', 'Propiedad UASLP', 'IMSS-Bienestar', 'Equipo Personal', 'Otros')");
    }
};
