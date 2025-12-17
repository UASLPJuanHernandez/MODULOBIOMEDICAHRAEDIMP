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
        Schema::create('auditoria_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auditoria_id')->constrained('auditorias')->cascadeOnDelete();
            $table->foreignId('mobiliario_id')->constrained('mobiliario')->cascadeOnDelete();
            $table->boolean('presente')->default(false);
            $table->text('comentarios')->nullable();
            $table->boolean('requiere_vale')->default(false);
            $table->string('folio_vale')->nullable();
            $table->timestamp('fecha_verificacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_items');
    }
};
