-- =====================================================================
--  Migracion 02 — agrega la clave foranea productos.categoria -> categorias.nombre.
--  Hace que la relacion entre productos y categorias exista formalmente y se
--  vea en DBeaver / phpMyAdmin. Correr UNA sola vez.
--
--  COMO CORRERLA:
--    docker exec -i tarea_crud_mysql sh -c 'mysql -u root -p"$MYSQL_ROOT_PASSWORD" tarea_crud_db' < database/migracion-02-fk-categoria.sql
--    (o pegarla en phpMyAdmin -> SQL)
-- =====================================================================

-- 1) Normaliza los datos antes de poner la regla: los productos con categoria
--    vacia ('') o con una categoria que NO existe en la tabla categorias quedan
--    en NULL (sin categoria). Si no, la FK los rechazaria.
UPDATE productos
   SET categoria = NULL
 WHERE categoria = ''
    OR (categoria IS NOT NULL AND categoria NOT IN (SELECT nombre FROM categorias));

-- 2) Agrega la clave foranea.
ALTER TABLE productos
  ADD CONSTRAINT fk_producto_categoria
  FOREIGN KEY (categoria) REFERENCES categorias(nombre)
  ON UPDATE CASCADE ON DELETE SET NULL;
