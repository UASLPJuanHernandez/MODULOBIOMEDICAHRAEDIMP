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
            $table->longText('firma_ingeniero')->nullable()->after('recibe_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('bitacoras_reporte', function (Blueprint $table) {
            $table->dropColumn('firma_ingeniero');
        });
    }
};
