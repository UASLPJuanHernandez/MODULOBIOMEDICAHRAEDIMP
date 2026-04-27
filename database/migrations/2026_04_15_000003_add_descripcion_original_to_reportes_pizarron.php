<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportes_pizarron', function (Blueprint $table) {
            $table->text('descripcion_original')->nullable()->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('reportes_pizarron', function (Blueprint $table) {
            $table->dropColumn('descripcion_original');
        });
    }
};
