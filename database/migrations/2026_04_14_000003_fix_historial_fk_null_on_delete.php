<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventario_equipo_historiales', function (Blueprint $table) {
            // Cambiar cascadeOnDelete → nullOnDelete para que los registros
            // de historial persistan cuando se elimina el equipo (con FK en null)
            $table->dropForeign(['inventario_equipo_id']);
            $table->foreign('inventario_equipo_id')
                  ->references('id')
                  ->on('inventario_equipos')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventario_equipo_historiales', function (Blueprint $table) {
            $table->dropForeign(['inventario_equipo_id']);
            $table->foreign('inventario_equipo_id')
                  ->references('id')
                  ->on('inventario_equipos')
                  ->cascadeOnDelete();
        });
    }
};
