<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Esta migración soluciona el error:
     * "Field 'movimiento_id' doesn't have a default value"
     * 
     * Los vales de resguardo no siempre tienen un movimiento asociado,
     * por lo que el campo movimiento_id debe ser nullable.
     */
    public function up(): void
    {
        Schema::table('vales', function (Blueprint $table) {
            // Hacer movimiento_id nullable
            $table->unsignedBigInteger('movimiento_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vales', function (Blueprint $table) {
            // Revertir a NOT NULL (solo si no hay registros con NULL)
            $table->unsignedBigInteger('movimiento_id')->nullable(false)->change();
        });
    }
};
