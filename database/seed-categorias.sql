-- Categorias iniciales. Ya vienen dentro de schema.sql; este archivo queda
-- como referencia por si se quieren recargar sueltas.
USE tarea_crud_db;
INSERT IGNORE INTO categorias (nombre) VALUES
  ('Almacenamiento'), ('Audio'), ('Monitores'), ('Perifericos');
