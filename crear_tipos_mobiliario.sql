-- Script SQL para crear tipos de mobiliario del sistema de Activo Fijo HRAE-DIMP
-- Fecha: 2026-02-04

-- ===================================================
-- 1. INSERTAR TIPOS DE MOBILIARIO
-- ===================================================

-- Equipos de Servicios de Salud
INSERT INTO tipo_mobiliario (tipo, categoria, prefijo, created_at, updated_at)
VALUES 
    ('Equipo Médico', 'Servicios de Salud', 'F', NOW(), NOW()),
    ('Equipo no Médico', 'Servicios de Salud', 'F', NOW(), NOW());

-- Equipos de Comodato
INSERT INTO tipo_mobiliario (tipo, categoria, prefijo, created_at, updated_at)
VALUES 
    ('Equipo Médico', 'Comodato', 'E', NOW(), NOW()),
    ('Equipo no Médico', 'Comodato', 'E', NOW(), NOW());

-- Equipos de Servicios Integrales
INSERT INTO tipo_mobiliario (tipo, categoria, prefijo, created_at, updated_at)
VALUES 
    ('Equipo Médico', 'Servicios Integrales', 'SI', NOW(), NOW()),
    ('Equipo no Médico', 'Servicios Integrales', 'SI', NOW(), NOW());

-- ===================================================
-- 2. VERIFICAR TIPOS DE MOBILIARIO CREADOS
-- ===================================================

SELECT 
    id,
    tipo,
    categoria,
    prefijo,
    created_at
FROM tipo_mobiliario
ORDER BY categoria, tipo;

-- ===================================================
-- RESUMEN DE TIPOS DE MOBILIARIO
-- ===================================================
-- 
-- CATEGORÍAS:
-- 
-- 1. Servicios de Salud (Prefijo: F)
--    - Equipo Médico
--    - Equipo no Médico
-- 
-- 2. Comodato (Prefijo: E)
--    - Equipo Médico
--    - Equipo no Médico
-- 
-- 3. Servicios Integrales (Prefijo: SI)
--    - Equipo Médico
--    - Equipo no Médico
-- 
-- TOTAL: 6 tipos de mobiliario
-- 
-- Los prefijos se utilizan para generar los códigos de inventario:
-- - F: Equipos de Servicios de Salud
-- - E: Equipos en Comodato
-- - SI: Equipos de Servicios Integrales
