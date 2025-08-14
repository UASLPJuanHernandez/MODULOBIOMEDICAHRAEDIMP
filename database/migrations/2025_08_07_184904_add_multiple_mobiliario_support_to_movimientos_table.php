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
        Schema::table('movimientos', function (Blueprint $table) {
            // Hacer mobiliario_id nullable para permitir movimientos con múltiples mobiliarios
            $table->foreignId('mobiliario_id')->nullable()->change();
            
            // Agregar campos para soporte de múltiples mobiliarios
            $table->string('numero_movimiento')->unique()->nullable()->after('id');
            $table->boolean('multiple_mobiliarios')->default(false)->after('mobiliario_id');
            $table->boolean('vale_generado')->default(false)->after('multiple_mobiliarios');
            $table->foreignId('vale_id')->nullable()->constrained('vales')->onDelete('set null')->after('vale_generado');
            
            // Índices
            $table->index('numero_movimiento');
            $table->index(['multiple_mobiliarios', 'vale_generado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropForeign(['vale_id']);
            $table->dropColumn(['numero_movimiento', 'multiple_mobiliarios', 'vale_generado', 'vale_id']);
            $table->foreignId('mobiliario_id')->nullable(false)->change();
        });
    }
};
