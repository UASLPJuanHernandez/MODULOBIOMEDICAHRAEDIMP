<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bitacoras_reporte', function (Blueprint $table) {
            $table->text('justificacion')->nullable()->after('mensaje_original');
        });
    }

    public function down(): void
    {
        Schema::table('bitacoras_reporte', function (Blueprint $table) {
            $table->dropColumn('justificacion');
        });
    }
};
