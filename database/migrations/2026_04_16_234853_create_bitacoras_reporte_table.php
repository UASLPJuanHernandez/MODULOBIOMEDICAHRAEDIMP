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
        Schema::create('bitacoras_reporte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_pizarron_id')->constrained('reportes_pizarron')->cascadeOnDelete();

            // Datos del solicitante
            $table->string('nombre_personal');
            $table->string('numero_identificacion')->nullable();
            $table->string('area_departamento');

            // Fechas
            $table->date('fecha_reporte');
            $table->time('hora_reporte');

            // Contenido
            $table->text('mensaje_original');
            $table->json('acciones')->nullable(); // [{texto, imagen_path, pie_imagen}]
            $table->string('resultado')->default('satisfactoria');

            // Equipo
            $table->string('nombre_dispositivo')->nullable();
            $table->string('numero_serie')->nullable();

            // Firmas
            $table->string('atiende_nombre')->nullable();
            $table->string('recibe_nombre')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacoras_reporte');
    }
};
