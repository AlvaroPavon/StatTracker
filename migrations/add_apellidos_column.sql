-- Migración: Añadir columna 'apellidos' a la tabla usuarios
-- Ejecutar este script si la tabla ya existe sin la columna apellidos

ALTER TABLE usuarios 
ADD COLUMN apellidos VARCHAR(100) NOT NULL DEFAULT '' 
AFTER nombre;

-- Confirmar que la columna fue añadida
-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'usuarios';
