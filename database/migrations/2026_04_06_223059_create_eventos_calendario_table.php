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
        Schema::create('eventos_calendario', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->datetime('fecha_inicio');
            $table->datetime('fecha_fin')->nullable();
            $table->boolean('todo_el_dia')->default(false);
            $table->string('ubicacion')->nullable();
            $table->string('responsable')->nullable();
            $table->enum('tipo', [
                'reunion', 'mantenimiento', 'inspeccion',
                'capacitacion', 'entrega', 'otro'
            ])->default('otro');
            $table->enum('estado', ['confirmado', 'tentativo', 'cancelado'])->default('confirmado');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->string('color')->default('#3b82f6');
            $table->text('notas')->nullable();
            $table->string('recurrencia')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos_calendario');
    }
};
