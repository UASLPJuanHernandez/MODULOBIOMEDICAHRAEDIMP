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
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobiliario_id')->constrained('mobiliario')->onDelete('cascade');
            $table->foreignId('area_actual_id')->constrained('localizacion')->onDelete('cascade');
            $table->foreignId('area_anterior_id')->nullable()->constrained('localizacion')->onDelete('set null');
            $table->timestamp('fecha_movimiento');
            $table->string('se_entrega_con');
            $table->string('se_retira_con');
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('usuario_id'); // Usuario que realizó el movimiento
            
            // Campo para control de concurrencia
            $table->integer('version')->default(1);
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['mobiliario_id', 'fecha_movimiento']);
            $table->index(['area_actual_id', 'fecha_movimiento']);
            $table->index('fecha_movimiento');
            $table->index('usuario_id');
            $table->index('version');
            
            // Foreign key para usuario_id
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
