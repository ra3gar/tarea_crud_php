# Base de datos

Base: **`tarea_crud_db`** · Motor: **MySQL 8.4** / InnoDB · Codificacion:
**utf8mb4** (`utf8mb4_general_ci`).

> El esquema **no se modifico** en la reestructuracion. La proteccion CSRF no
> necesita tablas ni columnas nuevas: el token vive en la sesion de PHP.

> **Diagrama entidad-relacion**:
> `DOCUMENTACION/recursos/diagramas/04-entidad-relacion.png`, generado a partir
> del `SHOW CREATE TABLE` real de la base en ejecucion.

> **Nota sobre `nombre` y `apellido` de `usuarios`**: en una base creada desde
> cero con `schema.sql` ambas columnas son `NOT NULL` sin valor por defecto. En
> una base que venga de `migracion-01-usuarios-roles.sql` quedan como
> `NOT NULL DEFAULT ''`, porque el `ALTER TABLE` necesitaba un valor para las
> filas que ya existian. La diferencia no afecta a la aplicacion: el PHP valida
> que ninguno de los dos llegue vacio.

## 1. Tabla `productos`

| Columna | Tipo | Nulo | Notas |
|---|---|---|---|
| `id` | INT AUTO_INCREMENT | no | **PK** |
| `nombre` | VARCHAR(100) | no | Obligatorio (validado tambien en PHP) |
| `descripcion` | VARCHAR(255) | si | |
| `precio` | DECIMAL(10,2) | no | DEFAULT 0.00. DECIMAL = dinero exacto, sin errores de redondeo |
| `stock` | INT | no | DEFAULT 0 |
| `categoria` | VARCHAR(50) | si | **FK** -> `categorias.nombre` |
| `fecha_creacion` | TIMESTAMP | — | DEFAULT CURRENT_TIMESTAMP |

## 2. Tabla `categorias`

| Columna | Tipo | Nulo | Notas |
|---|---|---|---|
| `id` | INT AUTO_INCREMENT | no | **PK** |
| `nombre` | VARCHAR(50) | no | **UNIQUE** — no se puede repetir |
| `fecha_creacion` | TIMESTAMP | — | DEFAULT CURRENT_TIMESTAMP |

## 3. Tabla `usuarios`

| Columna | Tipo | Nulo | Notas |
|---|---|---|---|
| `id` | INT AUTO_INCREMENT | no | **PK** |
| `usuario` | VARCHAR(50) | no | **UNIQUE** — es el login |
| `nombre` | VARCHAR(50) | no | Nombre real, separado del login |
| `apellido` | VARCHAR(50) | no | |
| `rol` | ENUM('admin','usuario') | no | DEFAULT 'usuario' |
| `password_hash` | VARCHAR(255) | no | Hash bcrypt. **Nunca** la clave en texto |
| `pregunta` | VARCHAR(255) | no | Pregunta de seguridad elegida |
| `respuesta_hash` | VARCHAR(255) | no | La respuesta tambien va hasheada |
| `fecha_creacion` | TIMESTAMP | — | DEFAULT CURRENT_TIMESTAMP |

## 4. Relacion e integridad referencial

```
categorias.nombre (UNIQUE)  <-----  productos.categoria
         constraint: fk_producto_categoria
         ON UPDATE CASCADE      -> renombrar la categoria arrastra los productos
         ON DELETE SET NULL     -> borrar la categoria deja los productos sin categoria
```

La FK apunta a `categorias.nombre` y no a `categorias.id` porque `productos`
guarda el nombre de la categoria. `nombre` es UNIQUE, que es lo que MySQL exige
para poder referenciarlo.

Consecuencia practica en el codigo: cuando el formulario llega sin categoria,
`ProductoController::paraGuardar()` guarda `null` y **no** cadena vacia, porque
`''` no existe en `categorias.nombre` y la FK lo rechazaria.

## 5. Indices

| Tabla | Indice | Origen |
|---|---|---|
| `productos` | PK `id` | clave primaria |
| `productos` | `categoria` | lo crea MySQL para la FK |
| `categorias` | PK `id` + UNIQUE `nombre` | |
| `usuarios` | PK `id` + UNIQUE `usuario` | |

## 6. Consultas importantes

**Listado con filtro combinado** (`Producto::todos`): el `WHERE` se arma en PHP
con `nombre LIKE :buscar` y/o `categoria = :categoria`; los valores van siempre
como parametros.

**Metricas del tablero** (`Producto::metricas`), una sola consulta:
```sql
SELECT COUNT(*)                                                 AS productos,
       COALESCE(SUM(stock), 0)                                  AS unidades,
       COALESCE(SUM(precio * stock), 0)                         AS valor,
       COALESCE(SUM(CASE WHEN stock < 10 THEN 1 ELSE 0 END), 0) AS bajos
FROM productos [WHERE ...]
```
`COALESCE` evita que una tabla vacia o un filtro sin resultados devuelva `NULL`
en lugar de 0. El umbral de stock bajo es **< 10**.

**Agrupado por categoria** (graficos): `COALESCE(NULLIF(categoria,''),'Sin
categoria')` junta en un mismo grupo los productos sin categoria.

**Categoria en uso** (`Categoria::enUso`), antes de borrar:
```sql
SELECT COUNT(*) FROM productos p
JOIN categorias c ON c.nombre = p.categoria
WHERE c.id = :id
```

## 7. Datos iniciales

`database/schema.sql` crea todo y siembra 5 productos de prueba, 4 categorias y
el administrador inicial. Lo ejecuta Docker **solo la primera vez** que se crea
el volumen (`/docker-entrypoint-initdb.d/`); en los arranques siguientes se
ignora, asi que los datos no se pierden.

Las credenciales del admin inicial estan en `docs/guia-instalacion.html`, que es
el manual oficial del sistema. No se repiten aca.

## 8. Respaldo y restauracion

```bash
# Respaldo (estructura + datos)
docker exec tarea_crud_mysql mysqldump -u root -p"$DB_PASS" \
  --databases tarea_crud_db > respaldo-$(date +%F).sql

# Restauracion
docker exec -i tarea_crud_mysql mysql -u root -p"$DB_PASS" < respaldo-2026-11-17.sql
```
Guarda los respaldos **fuera de `public/`**. Si quedaran dentro, `apache-deny.conf`
niega los `.sql` de todos modos, pero lo correcto es que no esten ahi.
