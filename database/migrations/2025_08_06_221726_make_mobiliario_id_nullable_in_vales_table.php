<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vales', function (Blueprint $table) {
            $table->unsignedBigInteger('mobiliario_id')->nullable()->change();
        });
        
        // Agregar 'resguardo' al enum tipo_vale
        DB::statement("ALTER TABLE vales MODIFY COLUMN tipo_vale ENUM('entrega', 'retiro', 'resguardo') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vales', function (Blueprint $table) {
            $table->unsignedBigInteger('mobiliario_id')->nullable(false)->change();
        });
        
        // Revertir el enum tipo_vale
        DB::statement("ALTER TABLE vales MODIFY COLUMN tipo_vale ENUM('entrega', 'retiro') NOT NULL");
    }
};
