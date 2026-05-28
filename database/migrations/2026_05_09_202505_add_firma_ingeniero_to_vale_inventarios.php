<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vale_inventarios', function (Blueprint $table) {
            $table->longText('firma_ingeniero')->nullable()->after('firma_imagen');
        });
    }

    public function down(): void
    {
        Schema::table('vale_inventarios', function (Blueprint $table) {
            $table->dropColumn('firma_ingeniero');
        });
    }
};
