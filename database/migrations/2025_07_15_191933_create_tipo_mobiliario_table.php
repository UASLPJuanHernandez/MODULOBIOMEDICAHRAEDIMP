<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tipo_mobiliario', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['Equipo Médico', 'Equipo no Médico']);
            $table->enum('categoria', ['Servicios de Salud', 'Comodato', 'Servicios Integrales']);
            $table->string('prefijo', 5); // 'F', 'E', 'SI'
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['tipo', 'categoria']);
            $table->index('categoria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_mobiliario');
    }
};
