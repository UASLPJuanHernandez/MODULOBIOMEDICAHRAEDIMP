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
        Schema::table('reportes_pizarron', function (Blueprint $table) {
            $table->string('nombre_dispositivo')->nullable()->after('equipo');
            $table->string('marca')->nullable()->after('nombre_dispositivo');
            $table->string('modelo')->nullable()->after('marca');
            $table->string('numero_serie')->nullable()->after('modelo');
            $table->string('numero_control')->nullable()->after('numero_serie');
            $table->string('tipo_servicio')->nullable()->after('numero_control'); // preventivo | correctivo
            $table->string('tipo_baja')->nullable()->after('tipo_servicio');     // no_funcional | inservible | obsoleto | disposicion | traspaso | otro
        });
    }

    public function down(): void
    {
        Schema::table('reportes_pizarron', function (Blueprint $table) {
            $table->dropColumn(['nombre_dispositivo','marca','modelo','numero_serie','numero_control','tipo_servicio','tipo_baja']);
        });
    }
};
