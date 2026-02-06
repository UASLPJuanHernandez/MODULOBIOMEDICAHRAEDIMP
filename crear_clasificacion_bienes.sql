-- Script SQL para crear clasificación de bienes del sistema de Activo Fijo HRAE-DIMP
-- Fecha: 2026-02-04
-- Basado en el Clasificador por Objeto del Gasto

-- ===================================================
-- 1. INSERTAR CLASIFICACIONES DE BIENES
-- ===================================================

-- GRUPO 5: Bienes Muebles, Inmuebles e Intangibles
-- Subgrupo 1 - Mobiliario y Equipo de Administración

INSERT INTO clasificacion_bienes (grupo, subgrupo, clase, nombre_grupo, descripcion_clase, created_at, updated_at)
VALUES
    (5, 1, 1, 'Bienes Muebles, Inmuebles e Intangibles', 'Muebles de oficina y estantería', NOW(), NOW()),
    (5, 1, 2, 'Bienes Muebles, Inmuebles e Intangibles', 'Muebles, excepto de oficina y estantería', NOW(), NOW()),
    (5, 1, 3, 'Bienes Muebles, Inmuebles e Intangibles', 'Bienes artísticos, culturales y científicos', NOW(), NOW()),
    (5, 1, 4, 'Bienes Muebles, Inmuebles e Intangibles', 'Objetos de valor', NOW(), NOW()),
    (5, 1, 5, 'Bienes Muebles, Inmuebles e Intangibles', 'Equipos de cómputo y tecnologías de la información', NOW(), NOW()),
    (5, 1, 6, 'Bienes Muebles, Inmuebles e Intangibles', 'Otros mobiliarios y equipos de administración', NOW(), NOW());

-- Subgrupo 2 - Mobiliario y Equipo Educacional y Recreativo

INSERT INTO clasificacion_bienes (grupo, subgrupo, clase, nombre_grupo, descripcion_clase, created_at, updated_at)
VALUES
    (5, 2, 1, 'Bienes Muebles, Inmuebles e Intangibles', 'Equipos y aparatos audiovisuales', NOW(), NOW()),
    (5, 2, 2, 'Bienes Muebles, Inmuebles e Intangibles', 'Aparatos deportivos', NOW(), NOW()),
    (5, 2, 3, 'Bienes Muebles, Inmuebles e Intangibles', 'Cámaras fotográficas y de video', NOW(), NOW()),
    (5, 2, 4, 'Bienes Muebles, Inmuebles e Intangibles', 'Otro mobiliario y equipo educacional y recreativo', NOW(), NOW());

-- Subgrupo 3 - Equipo e Instrumental Médico y de Laboratorio

INSERT INTO clasificacion_bienes (grupo, subgrupo, clase, nombre_grupo, descripcion_clase, created_at, updated_at)
VALUES
    (5, 3, 1, 'Bienes Muebles, Inmuebles e Intangibles', 'Equipo médico y de laboratorio', NOW(), NOW()),
    (5, 3, 2, 'Bienes Muebles, Inmuebles e Intangibles', 'Instrumental médico y de laboratorio', NOW(), NOW());

-- Subgrupo 4 - Vehículos y Equipo de Transporte

INSERT INTO clasificacion_bienes (grupo, subgrupo, clase, nombre_grupo, descripcion_clase, created_at, updated_at)
VALUES
    (5, 4, 1, 'Bienes Muebles, Inmuebles e Intangibles', 'Vehículos y equipo terrestres', NOW(), NOW()),
    (5, 4, 2, 'Bienes Muebles, Inmuebles e Intangibles', 'Carrocerías y remolques', NOW(), NOW()),
    (5, 4, 3, 'Bienes Muebles, Inmuebles e Intangibles', 'Equipo aeroespacial', NOW(), NOW()),
    (5, 4, 4, 'Bienes Muebles, Inmuebles e Intangibles', 'Equipo ferroviario', NOW(), NOW()),
    (5, 4, 5, 'Bienes Muebles, Inmuebles e Intangibles', 'Embarcaciones', NOW(), NOW()),
    (5, 4, 6, 'Bienes Muebles, Inmuebles e Intangibles', 'Otros equipos de transporte', NOW(), NOW());

-- Subgrupo 5 - Equipo de Defensa y Seguridad

INSERT INTO clasificacion_bienes (grupo, subgrupo, clase, nombre_grupo, descripcion_clase, created_at, updated_at)
VALUES
    (5, 5, 1, 'Bienes Muebles, Inmuebles e Intangibles', 'Equipo de defensa y seguridad', NOW(), NOW());

-- Subgrupo 6 - Maquinaria, Otros Equipos y Herramientas

INSERT INTO clasificacion_bienes (grupo, subgrupo, clase, nombre_grupo, descripcion_clase, created_at, updated_at)
VALUES
    (5, 6, 1, 'Bienes Muebles, Inmuebles e Intangibles', 'Maquinaria y equipo agropecuario', NOW(), NOW()),
    (5, 6, 2, 'Bienes Muebles, Inmuebles e Intangibles', 'Maquinaria y equipo industrial', NOW(), NOW()),
    (5, 6, 3, 'Bienes Muebles, Inmuebles e Intangibles', 'Maquinaria y equipo de construcción', NOW(), NOW()),
    (5, 6, 4, 'Bienes Muebles, Inmuebles e Intangibles', 'Sistemas de aire acondicionado, calefacción y de refrigeración industrial y comercial', NOW(), NOW()),
    (5, 6, 5, 'Bienes Muebles, Inmuebles e Intangibles', 'Equipo de comunicación y telecomunicación', NOW(), NOW()),
    (5, 6, 6, 'Bienes Muebles, Inmuebles e Intangibles', 'Equipos de generación eléctrica, aparatos y accesorios eléctricos', NOW(), NOW()),
    (5, 6, 7, 'Bienes Muebles, Inmuebles e Intangibles', 'Herramientas y máquinas-herramienta', NOW(), NOW()),
    (5, 6, 9, 'Bienes Muebles, Inmuebles e Intangibles', 'Otros equipos', NOW(), NOW());

-- GRUPO 6: Bienes Inmuebles

INSERT INTO clasificacion_bienes (grupo, subgrupo, clase, nombre_grupo, descripcion_clase, created_at, updated_at)
VALUES
    (6, 8, 3, 'Bienes Inmuebles', 'Edificios no habitacionales', NOW(), NOW());

-- GRUPO 7: Activos Biológicos

INSERT INTO clasificacion_bienes (grupo, subgrupo, clase, nombre_grupo, descripcion_clase, created_at, updated_at)
VALUES
    (7, 7, 1, 'Activos Biológicos', 'Bovinos', NOW(), NOW()),
    (7, 7, 2, 'Activos Biológicos', 'Porcinos', NOW(), NOW()),
    (7, 7, 3, 'Activos Biológicos', 'Aves', NOW(), NOW()),
    (7, 7, 4, 'Activos Biológicos', 'Ovinos y caprinos', NOW(), NOW()),
    (7, 7, 5, 'Activos Biológicos', 'Peces y acuicultura', NOW(), NOW()),
    (7, 7, 6, 'Activos Biológicos', 'Equinos', NOW(), NOW()),
    (7, 7, 7, 'Activos Biológicos', 'Especies menores', NOW(), NOW()),
    (7, 7, 8, 'Activos Biológicos', 'Árboles y plantas', NOW(), NOW()),
    (7, 7, 9, 'Activos Biológicos', 'Otros activos biológicos', NOW(), NOW());

-- ===================================================
-- 2. VERIFICAR CLASIFICACIONES CREADAS
-- ===================================================

SELECT 
    id,
    CONCAT(grupo, '.', subgrupo, '.', clase) AS codigo,
    nombre_grupo,
    descripcion_clase,
    created_at
FROM clasificacion_bienes
ORDER BY grupo, subgrupo, clase;

-- ===================================================
-- RESUMEN DE CLASIFICACIONES
-- ===================================================
-- 
-- GRUPO 5: Bienes Muebles, Inmuebles e Intangibles (33 clasificaciones)
--   - Subgrupo 1: Mobiliario y Equipo de Administración (6)
--   - Subgrupo 2: Mobiliario y Equipo Educacional y Recreativo (4)
--   - Subgrupo 3: Equipo e Instrumental Médico y de Laboratorio (2)
--   - Subgrupo 4: Vehículos y Equipo de Transporte (6)
--   - Subgrupo 5: Equipo de Defensa y Seguridad (1)
--   - Subgrupo 6: Maquinaria, Otros Equipos y Herramientas (8)
-- 
-- GRUPO 6: Bienes Inmuebles (1 clasificación)
--   - Subgrupo 8: Edificios (1)
-- 
-- GRUPO 7: Activos Biológicos (9 clasificaciones)
--   - Subgrupo 7: Activos Biológicos (9)
-- 
-- TOTAL: 43 clasificaciones de bienes
-- 
-- Formato de código: [grupo].[subgrupo].[clase]
-- Ejemplo: 5.3.1 = Equipo médico y de laboratorio
