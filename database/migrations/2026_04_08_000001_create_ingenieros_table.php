<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingenieros', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('cargo')->nullable();
            $table->string('cedula_profesional')->nullable();
            $table->string('email')->nullable();
            $table->text('firma_svg')->nullable();       // path SVG de la firma
            $table->string('firma_color', 20)->default('#1e3a8a');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingenieros');
    }
};
