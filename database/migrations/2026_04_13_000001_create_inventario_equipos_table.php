<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_equipos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_inventario')->nullable()->index();
            $table->string('clues')->nullable();
            $table->string('unidad_medica')->nullable();
            $table->string('area')->nullable()->index();
            $table->string('ubicacion_especifica')->nullable();
            $table->string('clave_cbsg')->nullable()->index();
            $table->string('equipo')->nullable()->index();
            $table->string('equipo_alternativo')->nullable();
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('numero_serie')->nullable();
            $table->string('propiedad')->nullable();
            $table->string('condiciones')->nullable();
            $table->string('estatus')->nullable()->index();
            $table->text('causa_no_funcionamiento')->nullable();
            $table->date('fecha_adquisicion')->nullable();
            $table->string('anio_fabricacion')->nullable();
            $table->text('requerimientos')->nullable();
            $table->string('frecuencia_mantenimiento')->nullable();
            $table->string('tipo_mantenimiento')->nullable();
            $table->string('contrato_mantenimiento')->nullable();
            $table->boolean('fin_vida_util')->default(false);
            $table->boolean('garantia')->default(false);
            $table->date('fin_garantia')->nullable();
            $table->boolean('tiene_contrato')->default(false);
            $table->string('numero_contrato')->nullable();
            $table->string('proveedor_mantenimiento')->nullable();
            $table->date('inicio_poliza')->nullable();
            $table->date('fin_poliza')->nullable();
            $table->string('costo_contrato')->nullable();
            $table->string('cantidad_mp_anio')->nullable();
            $table->date('ultimo_mp')->nullable();
            $table->date('siguiente_mp')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['equipo', 'marca', 'modelo']);
            $table->index(['estatus', 'condiciones']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_equipos');
    }
};
