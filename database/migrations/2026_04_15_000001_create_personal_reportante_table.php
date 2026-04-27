<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_reportante', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('numero_empleado')->unique();
            $table->string('servicio');
            $table->string('password');
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_reportante');
    }
};
