<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Limpiar tablas del módulo anterior
        Schema::dropIfExists('registro_valores');
        Schema::dropIfExists('formato_campos');

        // Simplificar registros: quitar equipo_id, agregar identificador y contenido_editado
        Schema::table('registros', function (Blueprint $table) {
            if (Schema::hasColumn('registros', 'equipo_id')) {
                $table->dropForeign(['equipo_id']);
                $table->dropColumn('equipo_id');
            }
            $table->string('identificador')->nullable()->after('formato_id');
            $table->longText('contenido_editado')->nullable()->after('identificador');
        });

        // Simplificar formatos: quitar contenido JSON, agregar contenido_texto
        Schema::table('formatos', function (Blueprint $table) {
            if (Schema::hasColumn('formatos', 'contenido')) {
                $table->dropColumn('contenido');
            }
            $table->text('contenido_texto')->nullable()->after('archivo_path');
        });
    }

    public function down(): void
    {
        Schema::table('formatos', function (Blueprint $table) {
            $table->dropColumn('contenido_texto');
            $table->json('contenido')->nullable();
        });
        Schema::table('registros', function (Blueprint $table) {
            $table->dropColumn(['identificador', 'contenido_editado']);
            $table->foreignId('equipo_id')->nullable()->constrained('mobiliario')->nullOnDelete();
        });
    }
};
