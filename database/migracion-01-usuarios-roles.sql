-- =====================================================================
--  Migracion 01 — agrega nombre, apellido y rol a la tabla usuarios.
--  Es un ALTER: NO borra datos, conserva los usuarios que ya existen.
--
--  COMO CORRERLA (elegi UNA opcion):
--   A) Terminal (Docker):
--      docker exec -i tarea_crud_mysql mysql -u root -p tarea_crud_db < database/migracion-01-usuarios-roles.sql
--   B) phpMyAdmin: pestana "SQL" -> pegar todo este texto -> Continuar.
-- =====================================================================

ALTER TABLE usuarios
  ADD COLUMN nombre   VARCHAR(50) NOT NULL DEFAULT '' AFTER usuario,
  ADD COLUMN apellido VARCHAR(50) NOT NULL DEFAULT '' AFTER nombre,
  ADD COLUMN rol      ENUM('admin','usuario') NOT NULL DEFAULT 'usuario' AFTER apellido;

-- Para que ningun usuario viejo quede con el saludo en blanco: usamos su
-- login como nombre provisional (se edita despues desde el panel).
UPDATE usuarios SET nombre = usuario WHERE nombre = '';

-- Garantiza al menos un administrador: el usuario mas antiguo (menor id).
UPDATE usuarios SET rol = 'admin' ORDER BY id LIMIT 1;

-- Usuario ADMINISTRADOR inicial (mismo que siembra schema.sql).
-- INSERT IGNORE: si ya existe un usuario 'admin', no hace nada (no falla).
--   Login: admin   Contrasena: Inicio01a   (hash bcrypt; la clave no va en texto)
INSERT IGNORE INTO usuarios (usuario, nombre, apellido, rol, password_hash, pregunta, respuesta_hash) VALUES
  ('admin', 'Administrador', 'del Sistema', 'admin',
   '$2y$10$FXnM2IUhle6MT4U3TFTUAO8PLVStqPLmAE1ObFCMwrMR27YigOX92',
   'En que ciudad naciste?',
   '$2y$10$JDadpe4fzjoGPrKjj26eYut0Of3r99cWKK6MtSN.qCl3MzbutSKgq');
