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
        Schema::create('localizacion', function (Blueprint $table) {
            $table->id();
            $table->string('direccion');
            $table->string('division');
            $table->string('sub_area');
            $table->string('ubicacion');
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['division', 'sub_area']);
            $table->index('division');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('localizacion');
    }
};
