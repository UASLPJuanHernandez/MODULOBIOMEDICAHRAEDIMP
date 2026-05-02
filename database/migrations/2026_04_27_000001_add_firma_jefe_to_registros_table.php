<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros', function (Blueprint $table) {
            // mantenimiento | reporte | vale | documento
            $table->string('tipo_documento')->nullable()->after('estado');
            $table->unsignedBigInteger('jefe_id')->nullable()->after('tipo_documento');
            $table->foreign('jefe_id')->references('id')->on('personal_reportante')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('registros', function (Blueprint $table) {
            $table->dropForeign(['jefe_id']);
            $table->dropColumn(['tipo_documento', 'jefe_id']);
        });
    }
};
