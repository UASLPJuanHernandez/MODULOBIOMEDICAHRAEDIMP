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
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobiliario_id')->constrained('mobiliario')->cascadeOnDelete();
            $table->datetime('fecha_programada');
            $table->text('motivo');
            $table->enum('tipo_mantenimiento', ['mantenimiento', 'proveedor'])->default('mantenimiento');
            $table->string('proveedor_nombre')->nullable();
            $table->enum('estado', ['pendiente', 'aceptado', 'completado', 'rechazado'])->default('pendiente');
            $table->foreignId('usuario_solicitante_id')->constrained('users');
            $table->foreignId('usuario_mantenimiento_id')->nullable()->constrained('users');
            $table->datetime('fecha_aceptacion')->nullable();
            $table->datetime('fecha_completado')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('folio_vale')->nullable()->unique();
            $table->timestamps();
            
            $table->index(['estado', 'fecha_programada']);
            $table->index(['mobiliario_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
