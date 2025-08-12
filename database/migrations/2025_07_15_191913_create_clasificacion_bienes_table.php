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
        Schema::create('clasificacion_bienes', function (Blueprint $table) {
            $table->id();
            $table->integer('grupo'); // 5, 6, 7
            $table->integer('subgrupo');
            $table->integer('clase');
            $table->string('nombre_grupo');
            $table->text('descripcion_clase');
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['grupo', 'subgrupo', 'clase']);
            $table->index('grupo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clasificacion_bienes');
    }
};
