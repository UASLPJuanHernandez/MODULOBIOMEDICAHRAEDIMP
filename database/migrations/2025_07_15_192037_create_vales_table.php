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
        Schema::create('vales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobiliario_id')->constrained('mobiliario')->onDelete('cascade');
            $table->foreignId('movimiento_id')->constrained('movimientos')->onDelete('cascade');
            $table->enum('tipo_vale', ['entrega', 'retiro']);
            $table->timestamp('fecha_generacion');
            $table->string('responsable_entrega');
            $table->string('responsable_recibe');
            $table->text('observaciones')->nullable();
            $table->string('archivo_pdf')->nullable();
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['mobiliario_id', 'tipo_vale']);
            $table->index(['movimiento_id', 'fecha_generacion']);
            $table->index('tipo_vale');
            $table->index('fecha_generacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vales');
    }
};
