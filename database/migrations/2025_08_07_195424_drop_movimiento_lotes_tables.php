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
        // Eliminar tabla intermedia primero
        Schema::dropIfExists('movimiento_lote_mobiliario');
        
        // Eliminar tabla principal
        Schema::dropIfExists('movimiento_lotes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar tabla principal
        Schema::create('movimiento_lotes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_lote')->unique();
            $table->string('descripcion_movimiento');
            $table->unsignedBigInteger('area_destino_id');
            $table->datetime('fecha_movimiento');
            $table->unsignedBigInteger('usuario_id');
            $table->string('se_entrega_con');
            $table->string('se_retira_con');
            $table->text('observacion')->nullable();
            $table->boolean('vale_generado')->default(false);
            $table->unsignedBigInteger('vale_id')->nullable();
            $table->timestamps();
            
            $table->foreign('area_destino_id')->references('id')->on('localizacions');
            $table->foreign('usuario_id')->references('id')->on('users');
            $table->foreign('vale_id')->references('id')->on('vales')->onDelete('set null');
        });
        
        // Restaurar tabla intermedia
        Schema::create('movimiento_lote_mobiliario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movimiento_lote_id');
            $table->unsignedBigInteger('mobiliario_id');
            $table->unsignedBigInteger('area_anterior_id')->nullable();
            $table->timestamps();
            
            $table->foreign('movimiento_lote_id')->references('id')->on('movimiento_lotes')->onDelete('cascade');
            $table->foreign('mobiliario_id')->references('id')->on('mobiliarios')->onDelete('cascade');
            $table->foreign('area_anterior_id')->references('id')->on('localizacions')->onDelete('set null');
        });
    }
};
