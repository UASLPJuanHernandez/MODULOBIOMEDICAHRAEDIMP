<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vale_inventarios', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');                          // 'entrega' | 'retiro'
            $table->foreignId('inventario_equipo_id')
                  ->nullable()
                  ->constrained('inventario_equipos')
                  ->nullOnDelete();
            // Datos cacheados para poder re-generar aunque se elimine el equipo
            $table->string('numero_inventario')->nullable();
            $table->string('equipo_nombre')->nullable();
            $table->string('area')->nullable();
            $table->string('unidad_medica')->nullable();
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('numero_serie')->nullable();
            // Quién generó el vale
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('usuario_nombre')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'created_at']);
            $table->index('inventario_equipo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vale_inventarios');
    }
};
