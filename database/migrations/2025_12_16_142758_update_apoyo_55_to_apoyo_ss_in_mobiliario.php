<?php
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite usa TEXT, no ENUM — no requiere modificación de columna
    }

    public function down(): void
    {
        // noop
    }
};
