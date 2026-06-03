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
        Schema::table('vale_inventarios', function (Blueprint $table) {
            $table->foreignId('consumible_id')
                  ->nullable()
                  ->after('inventario_equipo_id')
                  ->constrained('consumibles')
                  ->nullOnDelete();
            $table->unsignedInteger('cantidad_entregada')->nullable()->after('consumible_id');
        });
    }

    public function down(): void
    {
        Schema::table('vale_inventarios', function (Blueprint $table) {
            $table->dropForeign(['consumible_id']);
            $table->dropColumn(['consumible_id', 'cantidad_entregada']);
        });
    }
};
