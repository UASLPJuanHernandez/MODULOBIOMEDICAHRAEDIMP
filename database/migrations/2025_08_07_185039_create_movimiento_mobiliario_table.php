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
        Schema::create('movimiento_mobiliario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movimiento_id');
            $table->unsignedBigInteger('mobiliario_id');
            $table->unsignedBigInteger('area_anterior_id')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('movimiento_id')->references('id')->on('movimientos')->onDelete('cascade');
            $table->foreign('mobiliario_id')->references('id')->on('mobiliario')->onDelete('cascade');
            $table->foreign('area_anterior_id')->references('id')->on('localizacion')->onDelete('set null');
            
            // Índices únicos para evitar duplicados
            $table->unique(['movimiento_id', 'mobiliario_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_mobiliario');
    }
};
