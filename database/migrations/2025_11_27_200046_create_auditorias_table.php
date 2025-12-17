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
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ubicacion_id')->constrained('localizacion')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->string('responsable_nombre');
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin')->nullable();
            $table->enum('estado', ['en_progreso', 'completada'])->default('en_progreso');
            $table->text('observaciones_generales')->nullable();
            $table->integer('total_mobiliarios')->default(0);
            $table->integer('mobiliarios_presentes')->default(0);
            $table->integer('mobiliarios_ausentes')->default(0);
            $table->integer('vales_generados')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
