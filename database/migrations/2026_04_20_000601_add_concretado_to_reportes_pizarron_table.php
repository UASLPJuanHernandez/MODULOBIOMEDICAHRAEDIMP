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
            $table->boolean('concretado')->default(false)->after('minimizado');
            $table->timestamp('concretado_at')->nullable()->after('concretado');
        });
    }

    public function down(): void
    {
        Schema::table('reportes_pizarron', function (Blueprint $table) {
            $table->dropColumn(['concretado', 'concretado_at']);
        });
    }
};
