<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vales', function (Blueprint $table) {
            if (Schema::hasColumn('vales', 'movimiento_lote_id')) {
                try { $table->dropIndex(['movimiento_lote_id']); } catch (\Exception $e) {}
                try { $table->dropForeign(['movimiento_lote_id']); } catch (\Exception $e) {}
                $table->dropColumn('movimiento_lote_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vales', function (Blueprint $table) {
            $table->unsignedBigInteger('movimiento_lote_id')->nullable();
            $table->foreign('movimiento_lote_id')->references('id')->on('movimiento_lotes')->onDelete('set null');
        });
    }
};
