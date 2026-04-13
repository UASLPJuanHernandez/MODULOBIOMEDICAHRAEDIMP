<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formatos', function (Blueprint $table) {
            $table->json('campos_json')->nullable()->after('contenido_texto');
        });
    }

    public function down(): void
    {
        Schema::table('formatos', function (Blueprint $table) {
            $table->dropColumn('campos_json');
        });
    }
};
