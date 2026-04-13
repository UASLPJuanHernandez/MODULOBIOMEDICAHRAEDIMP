<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('formato_respuestas');
        Schema::dropIfExists('formatos');

        Schema::create('formatos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('archivo_original');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formatos');
    }
};
