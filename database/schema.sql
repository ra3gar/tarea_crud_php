-- =====================================================================
--  Proyecto : Tarea CRUD PHP  (Inventario de productos)
--  Archivo  : schema.sql
--  Que hace : crea la base, las tablas y datos de prueba, TODO de una vez.
--  Re-ejecutable: se puede correr varias veces sin romper nada.
-- =====================================================================

-- 1) Crear la base de datos SOLO si no existe (utf8mb4 = tildes, n, emojis).
CREATE DATABASE IF NOT EXISTS tarea_crud_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE tarea_crud_db;

-- =====================================================================
--  TABLA 1: productos  (el CRUD principal)
-- =====================================================================
DROP TABLE IF EXISTS productos;
CREATE TABLE productos (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  nombre         VARCHAR(100)  NOT NULL,
  descripcion    VARCHAR(255)  NULL,
  precio         DECIMAL(10,2) NOT NULL DEFAULT 0.00,   -- DECIMAL = dinero exacto
  stock          INT           NOT NULL DEFAULT 0,
  categoria      VARCHAR(50)   NULL,
  fecha_creacion TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO productos (nombre, descripcion, precio, stock, categoria) VALUES
  ('Teclado mecanico',   'Switches azules, retroiluminado',      29.99, 15, 'Perifericos'),
  ('Mouse inalambrico',  'Ergonomico, conexion 2.4GHz',          14.50, 30, 'Perifericos'),
  ('Monitor 24 pulgadas','Full HD 1080p, panel IPS',            129.00,  8, 'Monitores'),
  ('Memoria USB 64GB',   'USB 3.0, alta velocidad',               9.75, 50, 'Almacenamiento'),
  ('Audifonos',          'Con microfono y cancelacion de ruido', 39.90, 20, 'Audio');

-- =====================================================================
--  TABLA 2: usuarios  (login + recuperacion por pregunta de seguridad)
--  SEGURIDAD: se guardan HASHES, nunca la contrasena ni la respuesta en texto.
-- =====================================================================
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  usuario        VARCHAR(50)  NOT NULL UNIQUE,   -- no se puede repetir
  password_hash  VARCHAR(255) NOT NULL,          -- hash bcrypt de la contrasena
  pregunta       VARCHAR(255) NOT NULL,          -- pregunta de seguridad elegida
  respuesta_hash VARCHAR(255) NOT NULL,          -- hash de la respuesta (tambien secreta)
  fecha_creacion TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- Nota: los usuarios se crean desde la pagina de REGISTRO en PHP,
--       porque el hash se genera con password_hash() en el codigo.

-- =====================================================================
--  COMO EJECUTARLO (elegir UNA opcion):
--  A) phpMyAdmin (visual): pestana "Importar" -> elegir este archivo -> Continuar.
--  B) phpMyAdmin (pegar):  pestana "SQL" -> pegar todo este texto -> Continuar.
--  C) Terminal (Docker):   docker exec -i mysql_global mysql -u root -p < database/schema.sql
-- =====================================================================
