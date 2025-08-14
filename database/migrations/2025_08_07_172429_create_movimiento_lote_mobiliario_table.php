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
        Schema::create('movimiento_lote_mobiliario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movimiento_lote_id')->constrained('movimiento_lotes')->onDelete('cascade');
            $table->foreignId('mobiliario_id')->constrained('mobiliario')->onDelete('cascade');
            $table->foreignId('area_anterior_id')->nullable()->constrained('localizacion')->onDelete('set null');
            $table->timestamps();
            
            // Índices y restricciones
            $table->unique(['movimiento_lote_id', 'mobiliario_id'], 'mov_lote_mob_unique');
            $table->index('movimiento_lote_id');
            $table->index('mobiliario_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_lote_mobiliario');
    }
};
