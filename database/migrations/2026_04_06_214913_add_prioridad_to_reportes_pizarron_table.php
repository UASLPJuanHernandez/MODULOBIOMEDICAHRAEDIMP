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
        Schema::table('reportes_pizarron', function (Blueprint $table) {
            $table->enum('prioridad', ['baja', 'media', 'moderada', 'urgencia'])->default('baja')->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('reportes_pizarron', function (Blueprint $table) {
            $table->dropColumn('prioridad');
        });
    }
};
