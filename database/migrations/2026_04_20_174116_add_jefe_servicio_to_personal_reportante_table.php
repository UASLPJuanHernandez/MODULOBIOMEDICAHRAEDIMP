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
            $table->boolean('es_jefe_servicio')->default(false)->after('firma');
            $table->string('area_jefe_servicio')->nullable()->after('es_jefe_servicio');
        });
    }

    public function down(): void
    {
        Schema::table('personal_reportante', function (Blueprint $table) {
            $table->dropColumn(['es_jefe_servicio', 'area_jefe_servicio']);
        });
    }
};
