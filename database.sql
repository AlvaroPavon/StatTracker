-- database.sql

-- Estructura de la tabla `usuarios`
CREATE TABLE IF NOT EXISTS usuarios (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  profile_pic VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- DATOS DE PRUEBA PARA CI

-- Reinicia los contadores y limpia para asegurar tests idempotentes
-- Los tests individuales harán su propia limpieza, pero esto asegura un buen estado inicial
DELETE FROM metricas;
DELETE FROM usuarios;
ALTER TABLE usuarios AUTO_INCREMENT = 1;
ALTER TABLE metricas AUTO_INCREMENT = 1;

-- Usuario de prueba principal (ID=1). 
-- Necesario para ApiIntegrationTest y MetricsTest.
-- La contraseña 'password123' hasheada con bcrypt.
INSERT INTO usuarios (id, nombre, email, password, profile_pic) VALUES 
(1, 'Test User', 'test@example.com', '$2y$10$wKz0b9lM9pLz4mR0qV8mK.Lz4mR0qV8mK.Lz4mR0qV8mK.Lz4mR0qV8mK.', NULL); 

-- Usuario de prueba sin datos de métricas (ID=2), necesario para testGetHealthDataForUserWithNoDataReturnsEmptyArray.
INSERT INTO usuarios (id, nombre, email, password, profile_pic) VALUES 
(2, 'Second Test User', 'second@example.com', '$2y$10$wKz0b9lM9pLz4mR0qV8mK.Lz4mR0qV8mK.Lz4mR0qV8mK.Lz4mR0qV8mK.', NULL); 

-- Dato inicial de prueba para el usuario 1. (80 kg / 1.00m^2 = 80.0 IMC)
-- Usado por MetricsTest::testGetHealthDataSuccess.
INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) VALUES 
(1, 80.0, 1.00, 80.0, '2025-01-01');