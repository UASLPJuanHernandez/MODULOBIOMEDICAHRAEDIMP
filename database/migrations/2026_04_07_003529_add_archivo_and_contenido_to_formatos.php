<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formatos', function (Blueprint $table) {
            $table->string('archivo_path')->nullable()->after('archivo_original');
            $table->json('contenido')->nullable()->after('archivo_path');
        });
    }

    public function down(): void
    {
        Schema::table('formatos', function (Blueprint $table) {
            $table->dropColumn(['archivo_path', 'contenido']);
        });
    }
};
