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
        Schema::create('ordenes_servicio', function (Blueprint $table) {
            $table->id();
            $table->string('numero_orden')->unique(); // Consecutivo automático
            $table->date('fecha_orden');
            $table->foreignId('mobiliario_id')->constrained('mobiliario')->onDelete('cascade');
            $table->string('proveedor_servicio');
            $table->string('area_ubicacion');
            $table->string('nombre_equipo');
            $table->text('descripcion_falla');
            $table->text('trabajo_realizado')->nullable();
            $table->text('componentes_cambiados')->nullable();
            $table->text('componentes_agregados')->nullable();
            $table->enum('tipo_mantenimiento', ['Correctivo', 'Preventivo', 'Garantía']);
            $table->unsignedBigInteger('usuario_id');
            $table->enum('estado', ['Pendiente', 'En Proceso', 'Completada', 'Cancelada'])->default('Pendiente');
            $table->string('archivo_pdf')->nullable();
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['mobiliario_id', 'fecha_orden']);
            $table->index(['tipo_mantenimiento', 'estado']);
            $table->index('proveedor_servicio');
            $table->index('fecha_orden');
            $table->index('estado');
            $table->index('numero_orden');
            
            // Foreign key para usuario_id
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes_servicio');
    }
};
