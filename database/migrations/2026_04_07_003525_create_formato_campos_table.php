<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formato_campos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formato_id')->constrained('formatos')->cascadeOnDelete();
            $table->string('nombre');
            $table->enum('tipo', ['text', 'number', 'date', 'textarea', 'checkbox', 'select']);
            $table->string('seccion')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formato_campos');
    }
};
