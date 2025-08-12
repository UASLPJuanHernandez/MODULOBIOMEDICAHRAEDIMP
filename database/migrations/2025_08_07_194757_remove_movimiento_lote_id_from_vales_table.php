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
        Schema::table('vales', function (Blueprint $table) {
            // Verificar si la columna existe antes de eliminarla
            if (Schema::hasColumn('vales', 'movimiento_lote_id')) {
                // Intentar eliminar la foreign key si existe
                try {
                    $table->dropForeign(['movimiento_lote_id']);
                } catch (\Exception $e) {
                    // Si la foreign key no existe, continuar
                }
                $table->dropColumn('movimiento_lote_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vales', function (Blueprint $table) {
            // Restaurar la columna movimiento_lote_id en caso de rollback
            $table->unsignedBigInteger('movimiento_lote_id')->nullable();
            $table->foreign('movimiento_lote_id')->references('id')->on('movimiento_lotes')->onDelete('set null');
        });
    }
};
