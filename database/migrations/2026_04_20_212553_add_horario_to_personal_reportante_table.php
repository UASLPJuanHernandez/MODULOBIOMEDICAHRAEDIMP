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
        Schema::table('personal_reportante', function (Blueprint $table) {
            $table->time('horario_inicio')->nullable()->after('area_jefe_servicio');
            $table->time('horario_fin')->nullable()->after('horario_inicio');
        });
    }

    public function down(): void
    {
        Schema::table('personal_reportante', function (Blueprint $table) {
            $table->dropColumn(['horario_inicio', 'horario_fin']);
        });
    }
};
