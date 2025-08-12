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
        Schema::table('mobiliario', function (Blueprint $table) {
            $table->boolean('tiene_folio')->default(false)->after('metodo_adquisicion');
            $table->string('numero_folio')->nullable()->after('tiene_folio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobiliario', function (Blueprint $table) {
            $table->dropColumn(['tiene_folio', 'numero_folio']);
        });
    }
};
