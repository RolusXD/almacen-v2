-- ==============================================================================
-- MIGRACIÓN 005: Costos de Materiales y Estandarización de Destinos
-- ==============================================================================

-- 1. Actualizar costos de referencia en materiales/accesorios para cálculo de valor en almacén
UPDATE accesorios SET costo = 15.00 WHERE id_accesorio = 1 AND (costo IS NULL OR costo = 0);
UPDATE accesorios SET costo = 2.50 WHERE id_accesorio = 3 AND (costo IS NULL OR costo = 0);
UPDATE accesorios SET costo = 35.00 WHERE id_accesorio = 15 AND (costo IS NULL OR costo = 0);

-- 2. Asegurar que la tabla de destinos contenga los destinos estándar si no existen
INSERT INTO destinos (nombre, descripcion, activo)
SELECT 'INSTALACIONES', 'Equipos y materiales para instalaciones de clientes', 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE UPPER(TRIM(nombre)) = 'INSTALACIONES');

INSERT INTO destinos (nombre, descripcion, activo)
SELECT 'INFRAESTRUCTURA', 'Equipos y materiales para infraestructura, nodos y torres', 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE UPPER(TRIM(nombre)) = 'INFRAESTRUCTURA');

INSERT INTO destinos (nombre, descripcion, activo)
SELECT 'MANTENIMIENTO RED', 'Mantenimiento y soporte de redes de fibra y cableado', 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE UPPER(TRIM(nombre)) = 'MANTENIMIENTO RED');

INSERT INTO destinos (nombre, descripcion, activo)
SELECT 'MANTENIMIENTO VEHICULOS', 'Equipos y accesorios asignados a mantenimiento de vehículos', 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE UPPER(TRIM(nombre)) = 'MANTENIMIENTO VEHICULOS');

INSERT INTO destinos (nombre, descripcion, activo)
SELECT 'ADMINISTRACION', 'Materiales y equipos para administración y oficinas', 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE UPPER(TRIM(nombre)) = 'ADMINISTRACION');

INSERT INTO destinos (nombre, descripcion, activo)
SELECT 'SOPORTE TECNICO', 'Equipos y herramientas para cuadrillas de soporte técnico', 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE UPPER(TRIM(nombre)) = 'SOPORTE TECNICO');
