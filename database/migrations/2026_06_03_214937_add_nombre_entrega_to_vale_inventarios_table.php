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
        Schema::table('vale_inventarios', function (Blueprint $table) {
            $table->string('nombre_entrega')->nullable()->after('usuario_nombre');
            $table->string('cargo_entrega')->nullable()->after('nombre_entrega');
        });
    }

    public function down(): void
    {
        Schema::table('vale_inventarios', function (Blueprint $table) {
            $table->dropColumn(['nombre_entrega', 'cargo_entrega']);
        });
    }
};
