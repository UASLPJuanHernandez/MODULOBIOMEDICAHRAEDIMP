<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros', function (Blueprint $table) {
            $table->string('estado')->default('pendiente')->after('es_borrador'); // pendiente | en_curso
            $table->unsignedBigInteger('firmado_por_id')->nullable()->after('estado');
            $table->timestamp('firmado_at')->nullable()->after('firmado_por_id');

            $table->foreign('firmado_por_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('registros', function (Blueprint $table) {
            $table->dropForeign(['firmado_por_id']);
            $table->dropColumn(['estado', 'firmado_por_id', 'firmado_at']);
        });
    }
};
