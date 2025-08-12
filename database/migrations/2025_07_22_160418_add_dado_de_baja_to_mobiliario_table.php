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
            $table->boolean('dado_de_baja')->default(false)->after('estado_mobiliario');
            $table->timestamp('fecha_baja')->nullable()->after('dado_de_baja');
            $table->text('motivo_baja')->nullable()->after('fecha_baja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobiliario', function (Blueprint $table) {
            $table->dropColumn(['dado_de_baja', 'fecha_baja', 'motivo_baja']);
        });
    }
};
