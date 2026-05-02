<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros', function (Blueprint $table) {
            // JSON con {page, x, y, w, h, firma_svg} — posición elegida por el jefe en el portal
            $table->text('firma_jefe_data')->nullable()->after('jefe_id');
        });
    }

    public function down(): void
    {
        Schema::table('registros', function (Blueprint $table) {
            $table->dropColumn('firma_jefe_data');
        });
    }
};
