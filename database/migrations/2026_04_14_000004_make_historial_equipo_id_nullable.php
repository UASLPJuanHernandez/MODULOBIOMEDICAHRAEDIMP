<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite no soporta ALTER COLUMN directamente.
        // Recreamos la tabla con inventario_equipo_id nullable.
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('
            CREATE TABLE inventario_equipo_historiales_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                inventario_equipo_id INTEGER NULL,
                tipo_evento VARCHAR NOT NULL,
                cambios TEXT NULL,
                descripcion VARCHAR NULL,
                usuario_id INTEGER NULL,
                usuario_nombre VARCHAR NULL,
                ip_address VARCHAR NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                FOREIGN KEY (inventario_equipo_id) REFERENCES inventario_equipos(id) ON DELETE SET NULL,
                FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ');

        DB::statement('
            INSERT INTO inventario_equipo_historiales_new
            SELECT * FROM inventario_equipo_historiales
        ');

        DB::statement('DROP TABLE inventario_equipo_historiales');
        DB::statement('ALTER TABLE inventario_equipo_historiales_new RENAME TO inventario_equipo_historiales');

        // Recrear índices
        DB::statement('CREATE INDEX historial_equipo_fecha_idx ON inventario_equipo_historiales (inventario_equipo_id, created_at)');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('
            CREATE TABLE inventario_equipo_historiales_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                inventario_equipo_id INTEGER NOT NULL,
                tipo_evento VARCHAR NOT NULL,
                cambios TEXT NULL,
                descripcion VARCHAR NULL,
                usuario_id INTEGER NULL,
                usuario_nombre VARCHAR NULL,
                ip_address VARCHAR NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                FOREIGN KEY (inventario_equipo_id) REFERENCES inventario_equipos(id) ON DELETE CASCADE,
                FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ');

        DB::statement('
            INSERT INTO inventario_equipo_historiales_new
            SELECT * FROM inventario_equipo_historiales WHERE inventario_equipo_id IS NOT NULL
        ');

        DB::statement('DROP TABLE inventario_equipo_historiales');
        DB::statement('ALTER TABLE inventario_equipo_historiales_new RENAME TO inventario_equipo_historiales');
        DB::statement('CREATE INDEX historial_equipo_fecha_idx ON inventario_equipo_historiales (inventario_equipo_id, created_at)');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
