<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vale_inventarios', function (Blueprint $table) {
            $table->string('estado')->default('pendiente')->after('tipo');
            $table->foreignId('jefe_id')
                  ->nullable()
                  ->after('estado')
                  ->constrained('personal_reportantes')
                  ->nullOnDelete();
            $table->string('quien_recibe')->nullable()->after('usuario_nombre');
            $table->string('cargo_recibe')->nullable()->after('quien_recibe');
            $table->text('observaciones')->nullable()->after('cargo_recibe');
            $table->longText('firma_imagen')->nullable()->after('observaciones');
            $table->timestamp('firmado_at')->nullable()->after('firma_imagen');
            $table->timestamp('concretado_at')->nullable()->after('firmado_at');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::table('vale_inventarios', function (Blueprint $table) {
            $table->dropForeign(['jefe_id']);
            $table->dropIndex(['estado']);
            $table->dropColumn(['estado','jefe_id','quien_recibe','cargo_recibe','observaciones','firma_imagen','firmado_at','concretado_at']);
        });
    }
};
