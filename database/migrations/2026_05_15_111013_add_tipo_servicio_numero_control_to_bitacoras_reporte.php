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
        Schema::table('bitacoras_reporte', function (Blueprint $table) {
            $table->string('tipo_servicio')->nullable()->after('resultado');
            $table->string('numero_control')->nullable()->after('numero_serie');
        });
    }

    public function down(): void
    {
        Schema::table('bitacoras_reporte', function (Blueprint $table) {
            $table->dropColumn(['tipo_servicio', 'numero_control']);
        });
    }
};
