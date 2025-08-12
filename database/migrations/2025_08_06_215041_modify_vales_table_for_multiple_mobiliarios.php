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
        // Agregar campos de firma y matrícula a la tabla vales
        Schema::table('vales', function (Blueprint $table) {
            $table->string('firma_entrega')->nullable()->after('responsable_entrega');
            $table->string('matricula_entrega')->nullable()->after('firma_entrega');
            $table->string('firma_recibe')->nullable()->after('responsable_recibe');
            $table->string('matricula_recibe')->nullable()->after('firma_recibe');
            $table->string('numero_vale')->nullable()->after('id');
        });

        // Crear tabla intermedia para vales-mobiliarios (muchos a muchos)
        Schema::create('vale_mobiliario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->onDelete('cascade');
            $table->foreignId('mobiliario_id')->constrained('mobiliario')->onDelete('cascade');
            $table->timestamps();
            
            // Índices
            $table->index(['vale_id', 'mobiliario_id']);
            $table->unique(['vale_id', 'mobiliario_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar tabla intermedia
        Schema::dropIfExists('vale_mobiliario');
        
        // Eliminar campos agregados
        Schema::table('vales', function (Blueprint $table) {
            $table->dropColumn(['firma_entrega', 'matricula_entrega', 'firma_recibe', 'matricula_recibe', 'numero_vale']);
        });
    }
};
