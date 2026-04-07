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
        Schema::create('reportes_pizarron', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('equipo')->nullable();
            $table->string('ubicacion')->nullable();
            $table->text('descripcion');
            $table->enum('estado', ['pendiente', 'en_curso', 'completado'])->default('pendiente');
            $table->string('responsable')->nullable();
            $table->boolean('minimizado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes_pizarron');
    }
};
