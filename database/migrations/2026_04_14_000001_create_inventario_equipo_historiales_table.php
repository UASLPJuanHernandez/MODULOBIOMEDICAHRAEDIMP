<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_equipo_historiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_equipo_id')
                  ->constrained('inventario_equipos')
                  ->cascadeOnDelete();
            $table->string('tipo_evento'); // 'creado', 'actualizado', 'eliminado'
            $table->json('cambios')->nullable(); // [{campo, etiqueta, anterior, nuevo}, ...]
            $table->string('descripcion')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('usuario_nombre')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['inventario_equipo_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_equipo_historiales');
    }
};
