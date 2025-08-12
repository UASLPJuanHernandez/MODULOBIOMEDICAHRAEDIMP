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
        Schema::create('mobiliario', function (Blueprint $table) {
            $table->id();
            $table->string('numero_control')->unique(); // Con prefijo automático
            $table->foreignId('clasificacion_bienes_id')->constrained('clasificacion_bienes')->onDelete('cascade');
            $table->text('caracteristicas');
            $table->string('descripcion');
            $table->string('marca');
            $table->string('modelo');
            $table->string('numero_serie');
            $table->decimal('precio', 15, 2);
            $table->foreignId('tipo_mobiliario_id')->constrained('tipo_mobiliario')->onDelete('cascade');
            $table->foreignId('localizacion_id')->constrained('localizacion')->onDelete('cascade');
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('cascade');
            $table->enum('metodo_adquisicion', ['Compra', 'Donación', 'Apoyo 55', 'Comodato', 'Prestamo', 'Propiedad UASLP']);
            $table->decimal('depreciacion_registrada', 15, 2)->nullable();
            
            // Campos para control de concurrencia
            $table->integer('version')->default(1);
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->timestamp('locked_at')->nullable();
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['numero_control']);
            $table->index(['tipo_mobiliario_id', 'localizacion_id']);
            $table->index('metodo_adquisicion');
            $table->index(['marca', 'modelo']);
            $table->index('version');
            $table->index(['locked_by', 'locked_at']);
            
            // Foreign key para locked_by (referencia a users)
            $table->foreign('locked_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobiliario');
    }
};
