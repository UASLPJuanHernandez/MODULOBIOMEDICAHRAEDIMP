<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite no permite alterar FKs directamente; desactivamos la comprobación,
        // eliminamos la FK incorrecta (apunta a 'personal_reportantes' inexistente)
        // y la recreamos apuntando al nombre real 'personal_reportante'.
        Schema::disableForeignKeyConstraints();

        Schema::table('vale_inventarios', function (Blueprint $table) {
            $table->dropForeign(['jefe_id']);
        });

        Schema::table('vale_inventarios', function (Blueprint $table) {
            $table->foreign('jefe_id')
                ->references('id')
                ->on('personal_reportante')
                ->nullOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('vale_inventarios', function (Blueprint $table) {
            $table->dropForeign(['jefe_id']);
            $table->foreign('jefe_id')
                ->references('id')
                ->on('personal_reportantes')
                ->nullOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }
};
