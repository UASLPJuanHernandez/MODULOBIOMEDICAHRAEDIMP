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
            $table->string('area_departamento')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bitacoras_reporte', function (Blueprint $table) {
            $table->string('area_departamento')->nullable(false)->change();
        });
    }
};
