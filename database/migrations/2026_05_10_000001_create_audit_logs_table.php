<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 50);            // 'acceso', 'firma', 'usuario', 'sistema'
            $table->string('descripcion', 255);
            $table->string('actor_tipo', 20)->default('sistema'); // 'personal', 'admin', 'sistema'
            $table->unsignedBigInteger('actor_id')->default(0);
            $table->string('actor_nombre', 100);
            $table->string('documento_tipo', 50)->nullable(); // 'vale', 'solicitud', 'registro'
            $table->unsignedBigInteger('documento_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'created_at']);
            $table->index(['actor_tipo', 'actor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
