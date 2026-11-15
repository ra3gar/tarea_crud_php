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
