-- Script SQL para crear usuarios del sistema de Activo Fijo HRAE-DIMP
-- Fecha: 2026-02-04
-- IMPORTANTE: Este script asume que ya existen los roles en la tabla 'roles'

-- ===================================================
-- 1. INSERTAR USUARIOS
-- ===================================================

-- Usuario Administrador
INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at)
VALUES (
    'Administrador Sistema',
    'admin@inventario.hospital',
    '$2y$10$F1HP2FuIG1A184JxcskA0uoCYfs7amILFilXsNFEIueoUpzN03zYG', -- Hash de: admin123
    NOW(),
    NOW(),
    NOW()
);

-- Usuario Personal de Apoyo
INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at)
VALUES (
    'Personal de Apoyo',
    'apoyo@inventario.hospital',
    '$2y$10$mMPmCNmJKovS8GO.CaOizuupoysNvS3Sv/MBLMCHMTpLqH/qDQurO', -- Hash de: apoyo123
    NOW(),
    NOW(),
    NOW()
);

-- Usuario Jefe de Área
INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at)
VALUES (
    'Jefe de Área',
    'jefe@inventario.hospital',
    '$2y$10$u7rZWj36zQ2g81Dq.kVJqeCJvTldD//xnbYKA3/E3f98kr2UIzxmC', -- Hash de: jefe123
    NOW(),
    NOW(),
    NOW()
);

-- Usuario Personal de Mantenimiento
INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at)
VALUES (
    'Personal de Mantenimiento',
    'mantenimiento@inventario.hospital',
    '$2y$10$0n/RWthkqnGUrzx7N.g6s.rdD7LwVd0ZP4NVlVi.ucVY0G22Oa3ji', -- Hash de: mantenimiento123
    NOW(),
    NOW(),
    NOW()
);

-- ===================================================
-- 2. ASIGNAR ROLES A USUARIOS
-- ===================================================

-- Asignar rol de Administrador al primer usuario
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT 
    r.id,
    'App\\Models\\User',
    u.id
FROM roles r, users u
WHERE r.name = 'Administrador'
  AND u.email = 'admin@inventario.hospital'
  AND NOT EXISTS (
    SELECT 1 FROM model_has_roles mhr 
    WHERE mhr.model_id = u.id 
      AND mhr.role_id = r.id 
      AND mhr.model_type = 'App\\Models\\User'
  );

-- Asignar rol de Personal de Apoyo al segundo usuario
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT 
    r.id,
    'App\\Models\\User',
    u.id
FROM roles r, users u
WHERE r.name = 'Personal de Apoyo'
  AND u.email = 'apoyo@inventario.hospital'
  AND NOT EXISTS (
    SELECT 1 FROM model_has_roles mhr 
    WHERE mhr.model_id = u.id 
      AND mhr.role_id = r.id 
      AND mhr.model_type = 'App\\Models\\User'
  );

-- Asignar rol de Jefe de Área al tercer usuario
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT 
    r.id,
    'App\\Models\\User',
    u.id
FROM roles r, users u
WHERE r.name = 'Jefe de Área'
  AND u.email = 'jefe@inventario.hospital'
  AND NOT EXISTS (
    SELECT 1 FROM model_has_roles mhr 
    WHERE mhr.model_id = u.id 
      AND mhr.role_id = r.id 
      AND mhr.model_type = 'App\\Models\\User'
  );

-- Asignar rol de Personal de Mantenimiento al cuarto usuario
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT 
    r.id,
    'App\\Models\\User',
    u.id
FROM roles r, users u
WHERE r.name = 'Personal de Mantenimiento'
  AND u.email = 'mantenimiento@inventario.hospital'
  AND NOT EXISTS (
    SELECT 1 FROM model_has_roles mhr 
    WHERE mhr.model_id = u.id 
      AND mhr.role_id = r.id 
      AND mhr.model_type = 'App\\Models\\User'
  );

-- ===================================================
-- 3. VERIFICAR USUARIOS CREADOS
-- ===================================================

SELECT 
    u.id,
    u.name,
    u.email,
    r.name as rol,
    u.created_at
FROM users u
LEFT JOIN model_has_roles mhr ON u.id = mhr.model_id AND mhr.model_type = 'App\\Models\\User'
LEFT JOIN roles r ON mhr.role_id = r.id
WHERE u.email IN (
    'admin@inventario.hospital',
    'apoyo@inventario.hospital',
    'jefe@inventario.hospital',
    'mantenimiento@inventario.hospital'
)
ORDER BY u.id;

-- ===================================================
-- NOTAS IMPORTANTES:
-- ===================================================
-- 
-- Contraseñas de los usuarios:
-- - admin@inventario.hospital         -> admin123
-- - apoyo@inventario.hospital         -> apoyo123
-- - jefe@inventario.hospital          -> jefe123
-- - mantenimiento@inventario.hospital -> mantenimiento123
--
-- Los hashes de contraseñas son ejemplos. Para generar nuevos hashes:
-- php -r "echo password_hash('tu_contraseña', PASSWORD_BCRYPT);"
--
-- O desde Laravel:
-- php artisan tinker
-- >>> Hash::make('tu_contraseña')
--
-- Este script es seguro de ejecutar múltiples veces gracias a las 
-- cláusulas NOT EXISTS que evitan duplicados.
