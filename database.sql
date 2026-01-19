-- database.sql
-- StatTracker - Schema de Base de Datos con mejoras de seguridad

-- Estructura de la tabla `usuarios`
CREATE TABLE IF NOT EXISTS usuarios (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  apellidos VARCHAR(100) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  profile_pic VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  -- Índices para mejorar rendimiento
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de la tabla `metricas`
CREATE TABLE IF NOT EXISTS metricas (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) UNSIGNED NOT NULL,
  peso DECIMAL(5,2) NOT NULL,
  altura DECIMAL(3,2) NOT NULL,
  imc DECIMAL(5,2) NOT NULL,
  fecha_registro DATE NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  -- Clave foránea que enlaza con la tabla 'usuarios'
  FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  
  -- Índices para mejorar rendimiento
  INDEX idx_user_fecha (user_id, fecha_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migración: Añadir columna apellidos si no existe (para bases de datos existentes)
-- ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS apellidos VARCHAR(100) NOT NULL DEFAULT '' AFTER nombre;


-- DATOS DE PRUEBA PARA CI

-- Reinicia los contadores y limpia para asegurar tests idempotentes
DELETE FROM metricas;
DELETE FROM usuarios;
ALTER TABLE usuarios AUTO_INCREMENT = 1;
ALTER TABLE metricas AUTO_INCREMENT = 1;

-- Usuario de prueba principal (ID=1). 
-- Contraseña: 'Password123' (cumple requisitos de seguridad)
-- IMPORTANTE: Este hash fue generado con CryptoFortress::hashPassword() que incluye "pepper"
-- Para regenerar: cd /app && php -r "require 'vendor/autoload.php'; echo App\CryptoFortress::hashPassword('Password123');"
INSERT INTO usuarios (id, nombre, apellidos, email, password, profile_pic) VALUES 
(1, 'Test', 'User', 'test@example.com', '$argon2id$v=19$m=65536,t=4,p=4$TTNQR20xRFhkNnpPdzdQdg$/grb9q2sNxqjjJLL3oT4peQR608nmclvRdMbA1WxCR8', NULL); 

-- Usuario de prueba sin datos de métricas (ID=2)
-- Contraseña: 'Password123'
INSERT INTO usuarios (id, nombre, apellidos, email, password, profile_pic) VALUES 
(2, 'Second', 'Test User', 'second@example.com', '$argon2id$v=19$m=65536,t=4,p=4$TTNQR20xRFhkNnpPdzdQdg$/grb9q2sNxqjjJLL3oT4peQR608nmclvRdMbA1WxCR8', NULL); 

-- Dato inicial de prueba para el usuario 1. (80 kg / 1.00m^2 = 80.0 IMC)
INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) VALUES 
(1, 80.0, 1.00, 80.0, '2025-01-01');