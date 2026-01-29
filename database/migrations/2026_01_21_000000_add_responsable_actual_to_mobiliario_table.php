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
            $table->string('responsable_actual')->nullable()->after('depreciacion_registrada');
            $table->string('matricula_responsable')->nullable()->after('responsable_actual');
            $table->string('puesto_responsable')->nullable()->after('matricula_responsable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobiliario', function (Blueprint $table) {
            $table->dropColumn(['responsable_actual', 'matricula_responsable', 'puesto_responsable']);
        });
    }
};
