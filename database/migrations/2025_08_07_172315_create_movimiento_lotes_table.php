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
        Schema::create('movimiento_lotes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_lote')->unique(); // MOV-YYYY-NNNN
            $table->foreignId('area_actual_id')->constrained('localizacion')->onDelete('cascade');
            $table->foreignId('area_anterior_id')->nullable()->constrained('localizacion')->onDelete('set null');
            $table->timestamp('fecha_movimiento');
            $table->string('se_entrega_con');
            $table->string('se_retira_con');
            $table->text('observacion')->nullable();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->boolean('vale_generado')->default(false);
            $table->foreignId('vale_id')->nullable()->constrained('vales')->onDelete('set null');
            $table->timestamps();
            
            // Índices
            $table->index(['fecha_movimiento', 'vale_generado']);
            $table->index('numero_lote');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_lotes');
    }
};
