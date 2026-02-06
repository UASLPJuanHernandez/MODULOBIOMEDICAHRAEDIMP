-- Script SQL para crear localizaciones del sistema de Activo Fijo HRAE-DIMP
-- Fecha: 2026-02-04

-- ===================================================
-- 1. INSERTAR LOCALIZACIONES
-- ===================================================

-- Urgencias (Planta Baja)

INSERT INTO localizacion (direccion, division, sub_area, ubicacion, created_at, updated_at)
VALUES
    ('Planta Baja', 'Urgencias', 'Triage', 'Área de Clasificación', NOW(), NOW()),
    ('Planta Baja', 'Urgencias', 'Consultorios', 'Consultorio 1', NOW(), NOW()),
    ('Planta Baja', 'Urgencias', 'Consultorios', 'Consultorio 2', NOW(), NOW()),
    ('Planta Baja', 'Urgencias', 'Observación', 'Camas de Observación', NOW(), NOW());

-- Hospitalización (Primer Piso)

INSERT INTO localizacion (direccion, division, sub_area, ubicacion, created_at, updated_at)
VALUES
    ('Primer Piso', 'Hospitalización', 'Medicina Interna', 'Habitación 101', NOW(), NOW()),
    ('Primer Piso', 'Hospitalización', 'Medicina Interna', 'Habitación 102', NOW(), NOW()),
    ('Primer Piso', 'Hospitalización', 'Cirugía', 'Habitación 201', NOW(), NOW()),
    ('Primer Piso', 'Hospitalización', 'Cirugía', 'Habitación 202', NOW(), NOW());

-- Quirófanos (Segundo Piso)

INSERT INTO localizacion (direccion, division, sub_area, ubicacion, created_at, updated_at)
VALUES
    ('Segundo Piso', 'Quirófanos', 'Cirugía General', 'Quirófano 1', NOW(), NOW()),
    ('Segundo Piso', 'Quirófanos', 'Cirugía General', 'Quirófano 2', NOW(), NOW()),
    ('Segundo Piso', 'Quirófanos', 'Cirugía Especializada', 'Quirófano 3', NOW(), NOW());

-- Laboratorio (Planta Baja)

INSERT INTO localizacion (direccion, division, sub_area, ubicacion, created_at, updated_at)
VALUES
    ('Planta Baja', 'Laboratorio', 'Química Clínica', 'Lab. Química', NOW(), NOW()),
    ('Planta Baja', 'Laboratorio', 'Hematología', 'Lab. Hematología', NOW(), NOW()),
    ('Planta Baja', 'Laboratorio', 'Microbiología', 'Lab. Microbiología', NOW(), NOW());

-- Imagenología (Planta Baja)

INSERT INTO localizacion (direccion, division, sub_area, ubicacion, created_at, updated_at)
VALUES
    ('Planta Baja', 'Imagenología', 'Rayos X', 'Sala de Rayos X', NOW(), NOW()),
    ('Planta Baja', 'Imagenología', 'Ultrasonido', 'Sala de Ultrasonido', NOW(), NOW()),
    ('Planta Baja', 'Imagenología', 'Tomografía', 'Sala de TAC', NOW(), NOW());

-- Consulta Externa (Planta Baja)

INSERT INTO localizacion (direccion, division, sub_area, ubicacion, created_at, updated_at)
VALUES
    ('Planta Baja', 'Consulta Externa', 'Medicina General', 'Consultorio A', NOW(), NOW()),
    ('Planta Baja', 'Consulta Externa', 'Pediatría', 'Consultorio B', NOW(), NOW()),
    ('Planta Baja', 'Consulta Externa', 'Ginecología', 'Consultorio C', NOW(), NOW());

-- Administración (Tercer Piso)

INSERT INTO localizacion (direccion, division, sub_area, ubicacion, created_at, updated_at)
VALUES
    ('Tercer Piso', 'Administración', 'Dirección', 'Oficina del Director', NOW(), NOW()),
    ('Tercer Piso', 'Administración', 'Recursos Humanos', 'Oficina de RH', NOW(), NOW()),
    ('Tercer Piso', 'Administración', 'Activo Fijo', 'Oficina de Activo Fijo', NOW(), NOW());

-- Almacén (Sótano)

INSERT INTO localizacion (direccion, division, sub_area, ubicacion, created_at, updated_at)
VALUES
    ('Sótano', 'Almacén', 'Almacén General', 'Área de Medicamentos', NOW(), NOW()),
    ('Sótano', 'Almacén', 'Almacén General', 'Área de Material Médico', NOW(), NOW()),
    ('Sótano', 'Almacén', 'Almacén General', 'Área de Equipos', NOW(), NOW());

-- ===================================================
-- 2. VERIFICAR LOCALIZACIONES CREADAS
-- ===================================================

SELECT 
    id,
    direccion,
    division,
    sub_area,
    ubicacion,
    created_at
FROM localizacion
ORDER BY 
    CASE direccion
        WHEN 'Sótano' THEN 1
        WHEN 'Planta Baja' THEN 2
        WHEN 'Primer Piso' THEN 3
        WHEN 'Segundo Piso' THEN 4
        WHEN 'Tercer Piso' THEN 5
    END,
    division,
    sub_area;

-- ===================================================
-- RESUMEN DE LOCALIZACIONES
-- ===================================================
-- 
-- ESTRUCTURA POR PISO:
-- 
-- Sótano (3 localizaciones)
--   └── Almacén
--       └── Almacén General (Medicamentos, Material Médico, Equipos)
-- 
-- Planta Baja (16 localizaciones)
--   ├── Urgencias (Triage, Consultorios, Observación)
--   ├── Laboratorio (Química, Hematología, Microbiología)
--   ├── Imagenología (Rayos X, Ultrasonido, Tomografía)
--   └── Consulta Externa (Medicina General, Pediatría, Ginecología)
-- 
-- Primer Piso (4 localizaciones)
--   └── Hospitalización
--       ├── Medicina Interna (Hab. 101, 102)
--       └── Cirugía (Hab. 201, 202)
-- 
-- Segundo Piso (3 localizaciones)
--   └── Quirófanos
--       ├── Cirugía General (Quirófano 1, 2)
--       └── Cirugía Especializada (Quirófano 3)
-- 
-- Tercer Piso (3 localizaciones)
--   └── Administración
--       ├── Dirección
--       ├── Recursos Humanos
--       └── Activo Fijo
-- 
-- TOTAL: 29 localizaciones
-- 
-- DIVISIONES:
-- - Urgencias
-- - Hospitalización
-- - Quirófanos
-- - Laboratorio
-- - Imagenología
-- - Consulta Externa
-- - Administración
-- - Almacén
