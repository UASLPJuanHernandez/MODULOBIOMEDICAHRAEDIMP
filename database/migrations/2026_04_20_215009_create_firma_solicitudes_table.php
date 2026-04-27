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
        Schema::create('firma_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_pizarron_id')->constrained('reportes_pizarron')->cascadeOnDelete();
            $table->foreignId('personal_reportante_id')->constrained('personal_reportante')->cascadeOnDelete();
            $table->enum('estado', ['pendiente', 'firmado'])->default('pendiente');
            $table->timestamp('firmado_at')->nullable();
            $table->text('firma_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firma_solicitudes');
    }
};
