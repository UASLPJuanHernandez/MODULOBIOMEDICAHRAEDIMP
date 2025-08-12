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
        Schema::table('mobiliario', function (Blueprint $table) {
            $table->string('estado_mobiliario')->after('metodo_adquisicion');
            $table->boolean('tiene_accesorios')->default(false)->after('estado_mobiliario');
            $table->text('descripcion_accesorios')->nullable()->after('tiene_accesorios');
            $table->string('numero_serie_registrado')->nullable()->after('numero_serie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobiliario', function (Blueprint $table) {
            $table->dropColumn([
                'estado_mobiliario',
                'tiene_accesorios', 
                'descripcion_accesorios',
                'numero_serie_registrado'
            ]);
        });
    }
};
