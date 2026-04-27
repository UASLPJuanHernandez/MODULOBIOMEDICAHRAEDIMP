<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportes_pizarron', function (Blueprint $table) {
            $table->foreignId('personal_reportante_id')->nullable()->constrained('personal_reportante')->nullOnDelete()->after('responsable');
            $table->string('reportante_nombre')->nullable()->after('personal_reportante_id');
            $table->string('reportante_servicio')->nullable()->after('reportante_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('reportes_pizarron', function (Blueprint $table) {
            $table->dropForeign(['personal_reportante_id']);
            $table->dropColumn(['personal_reportante_id', 'reportante_nombre', 'reportante_servicio']);
        });
    }
};
