<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_valores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registro_id')->constrained('registros')->cascadeOnDelete();
            $table->foreignId('campo_id')->constrained('formato_campos')->cascadeOnDelete();
            $table->text('valor');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_valores');
    }
};
