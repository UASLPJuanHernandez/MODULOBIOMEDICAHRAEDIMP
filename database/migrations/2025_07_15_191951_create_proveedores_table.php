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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partida_id')->constrained('tipo_partida')->onDelete('cascade');
            $table->string('nombre_proveedor');
            $table->decimal('monto_unitario', 15, 2);
            $table->decimal('monto_total', 15, 2);
            $table->integer('cantidad_mobiliario');
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index('nombre_proveedor');
            $table->index(['nombre_proveedor', 'partida_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
