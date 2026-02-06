-- Script SQL para crear roles y permisos del sistema de Activo Fijo HRAE-DIMP
-- Fecha: 2026-02-04
-- Sistema de permisos basado en Spatie Laravel Permission

-- ===================================================
-- 1. CREAR PERMISOS
-- ===================================================

-- Permisos de Mobiliario
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('view mobiliario', 'web', NOW(), NOW()),
('create mobiliario', 'web', NOW(), NOW()),
('edit mobiliario', 'web', NOW(), NOW()),
('delete mobiliario', 'web', NOW(), NOW()),
('generate qr mobiliario', 'web', NOW(), NOW());

-- Permisos de Movimientos
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('view movimientos', 'web', NOW(), NOW()),
('create movimientos', 'web', NOW(), NOW()),
('edit movimientos', 'web', NOW(), NOW()),
('delete movimientos', 'web', NOW(), NOW());

-- Permisos de Vales
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('view vales', 'web', NOW(), NOW()),
('create vales', 'web', NOW(), NOW()),
('generate vales', 'web', NOW(), NOW());

-- Permisos de Órdenes de Servicio
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('view ordenes servicio', 'web', NOW(), NOW()),
('create ordenes servicio', 'web', NOW(), NOW()),
('edit ordenes servicio', 'web', NOW(), NOW()),
('delete ordenes servicio', 'web', NOW(), NOW()),
('generate ordenes servicio', 'web', NOW(), NOW());

-- Permisos de Reportes
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('view reportes', 'web', NOW(), NOW()),
('generate reportes', 'web', NOW(), NOW()),
('export reportes', 'web', NOW(), NOW());

-- Permisos de Configuración
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('view configuracion', 'web', NOW(), NOW()),
('edit configuracion', 'web', NOW(), NOW());

-- Permisos de Usuarios
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('view usuarios', 'web', NOW(), NOW()),
('create usuarios', 'web', NOW(), NOW()),
('edit usuarios', 'web', NOW(), NOW()),
('delete usuarios', 'web', NOW(), NOW());

-- Permisos de Dashboard
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('view dashboard', 'web', NOW(), NOW());

-- Permisos de Mantenimiento
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('view_mantenimientos', 'web', NOW(), NOW()),
('create_mantenimientos', 'web', NOW(), NOW()),
('edit_mantenimientos', 'web', NOW(), NOW()),
('aceptar_mantenimientos', 'web', NOW(), NOW()),
('completar_mantenimientos', 'web', NOW(), NOW()),
('rechazar_mantenimientos', 'web', NOW(), NOW()),
('generar_vales_mantenimiento', 'web', NOW(), NOW());

-- ===================================================
-- 2. CREAR ROLES
-- ===================================================

INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES
('Administrador', 'web', NOW(), NOW()),
('Personal de Apoyo', 'web', NOW(), NOW()),
('Jefe de Área', 'web', NOW(), NOW()),
('Personal de Mantenimiento', 'web', NOW(), NOW());

-- ===================================================
-- 3. ASIGNAR PERMISOS AL ROL ADMINISTRADOR (TODOS)
-- ===================================================

INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM permissions p
CROSS JOIN roles r
WHERE r.name = 'Administrador';

-- ===================================================
-- 4. ASIGNAR PERMISOS AL ROL PERSONAL DE APOYO
-- ===================================================

INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM permissions p
CROSS JOIN roles r
WHERE r.name = 'Personal de Apoyo'
  AND p.name IN (
    'view mobiliario',
    'create mobiliario',
    'edit mobiliario',
    'generate qr mobiliario',
    'view movimientos',
    'create movimientos',
    'edit movimientos',
    'view vales',
    'create vales',
    'generate vales',
    'view ordenes servicio',
    'create ordenes servicio',
    'edit ordenes servicio',
    'generate ordenes servicio',
    'view reportes',
    'generate reportes',
    'export reportes',
    'view dashboard'
  );

-- ===================================================
-- 5. ASIGNAR PERMISOS AL ROL JEFE DE ÁREA (SOLO LECTURA)
-- ===================================================

INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM permissions p
CROSS JOIN roles r
WHERE r.name = 'Jefe de Área'
  AND p.name IN (
    'view mobiliario',
    'view movimientos',
    'view vales',
    'view ordenes servicio',
    'view reportes',
    'view dashboard'
  );

-- ===================================================
-- 6. ASIGNAR PERMISOS AL ROL PERSONAL DE MANTENIMIENTO
-- ===================================================

INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM permissions p
CROSS JOIN roles r
WHERE r.name = 'Personal de Mantenimiento'
  AND p.name IN (
    'view_mantenimientos',
    'create_mantenimientos',
    'edit_mantenimientos',
    'aceptar_mantenimientos',
    'completar_mantenimientos',
    'rechazar_mantenimientos',
    'generar_vales_mantenimiento',
    'view ordenes servicio',
    'create ordenes servicio',
    'edit ordenes servicio'
  );

-- ===================================================
-- 7. VERIFICAR ROLES Y PERMISOS CREADOS
-- ===================================================

-- Total de permisos por rol
SELECT 
    r.name AS rol,
    COUNT(rhp.permission_id) AS total_permisos
FROM roles r
LEFT JOIN role_has_permissions rhp ON r.id = rhp.role_id
GROUP BY r.id, r.name
ORDER BY r.name;

-- Detalle de permisos por rol
SELECT 
    r.name AS rol,
    p.name AS permiso
FROM roles r
INNER JOIN role_has_permissions rhp ON r.id = rhp.role_id
INNER JOIN permissions p ON rhp.permission_id = p.id
ORDER BY r.name, p.name;

-- ===================================================
-- RESUMEN DEL SISTEMA DE PERMISOS
-- ===================================================
-- 
-- ROLES:
-- 
-- 1. Administrador (Jefe de Activo Fijo)
--    - Acceso completo a todas las funcionalidades
--    - Gestión de usuarios, configuración y eliminaciones
--    - Incluye todos los permisos de mantenimiento
--    - Total: 44 permisos
-- 
-- 2. Personal de Apoyo
--    - Puede crear, editar y visualizar mobiliario
--    - Gestión de movimientos, vales y órdenes de servicio
--    - Generación de reportes y QR
--    - NO puede: eliminar mobiliario, gestionar usuarios, configuración
--    - Total: 18 permisos
-- 
-- 3. Jefe de Área
--    - Solo visualización (modo lectura)
--    - Acceso a dashboard y reportes
--    - NO puede: crear, editar, eliminar o generar
--    - Total: 6 permisos
-- 
-- 4. Personal de Mantenimiento
--    - Gestión completa de mantenimientos
--    - Puede aceptar, completar y rechazar solicitudes
--    - Generación de vales de mantenimiento
--    - Gestión de órdenes de servicio
--    - Total: 10 permisos
-- 
-- PERMISOS TOTALES: 44
-- Organizados en categorías:
--   - Mobiliario (5 permisos)
--   - Movimientos (4 permisos)
--   - Vales (3 permisos)
--   - Órdenes de Servicio (5 permisos)
--   - Reportes (3 permisos)
--   - Configuración (2 permisos)
--   - Usuarios (4 permisos)
--   - Dashboard (1 permiso)
--   - Mantenimiento (7 permisos)
