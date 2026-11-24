# Matriz de dependencias — Tarea_CRUD_PHP

Anexo de la *Guia tecnica de arquitectura, estructura y codigo fuente* (v1.0, 2026-11-16).

**Leyenda:** `R` require/include · `U` use o llamada estatica · `E` extends · `N` instanciacion (`new`) · `f` uso de funcion global.

## 1. Matriz archivo a archivo

Se lee por filas: *el archivo de la fila depende de los marcados en las columnas*.

| Depende de → | autoload | helpers | Router | Controller | Database | config | Auth | Admin | M:Producto | M:Categoria | M:Usuario | Views | layouts |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| `public/index.php` | R | R | N | · | · | · | · | · | · | · | · | · | · |
| `routes/web.php` | · | · | · | · | · | · | · | · | · | · | · | · | · |
| `app/Core/Router.php` | · | · | · | · | · | · | U | · | · | · | · | · | · |
| `app/Core/Controller.php` | · | f | · | · | · | · | · | · | · | · | · | R | · |
| `app/Core/Database.php` | · | · | · | · | · | R | · | · | · | · | · | · | · |
| `app/Core/helpers.php` | · | · | · | · | · | · | · | · | · | · | · | · | · |
| `app/Core/autoload.php` | · | · | · | · | · | · | · | · | · | · | · | · | · |
| `app/Middleware/Auth.php` | · | f | · | · | · | · | · | · | · | · | · | · | · |
| `app/Middleware/Admin.php` | · | f | · | · | · | · | · | · | · | · | · | · | · |
| `ProductoController` | · | f | · | E | · | · | · | · | U | U | · | · | · |
| `CategoriaController` | · | f | · | E | · | · | · | · | · | U | · | · | · |
| `AuthController` | · | f | · | E | · | · | · | · | · | · | U | · | · |
| `UsuarioController` | · | f | · | E | · | · | · | U | · | · | U | · | · |
| `app/Models/Producto.php` | · | · | · | · | U | · | · | · | · | · | · | · | · |
| `app/Models/Categoria.php` | · | · | · | · | U | · | · | · | · | · | · | · | · |
| `app/Models/Usuario.php` | · | · | · | · | U | · | · | · | · | · | · | · | · |
| `app/Views/productos/*` | · | f | · | · | · | · | · | · | · | · | · | · | R |
| `app/Views/categorias/*` | · | f | · | · | · | · | · | · | · | · | · | · | R |
| `app/Views/auth/*` | · | f | · | · | · | · | · | · | · | · | · | · | R |
| `app/Views/usuarios/*` | · | f | · | · | · | · | · | · | · | · | · | · | R |
| `app/Views/layouts/header.php` | · | f | · | · | · | · | · | · | · | · | · | · | · |
| `includes/* (codigo muerto)` | · | · | · | · | · | · | · | · | · | · | · | · | · |

## 2. Controladores contra modelos, vistas y guardias

| Controlador | Producto | Categoria | Usuario | Vistas | Guardias |
|---|---|---|---|---|---|
| `ProductoController` | Si | Si (`nombres`) | — | `productos/` (4) | `Auth` (tabla de rutas) |
| `CategoriaController` | — | Si | — | `categorias/index` | `Auth` (tabla de rutas) |
| `AuthController` | — | — | Si | `auth/` (3) | **Ninguno** (rutas publicas) |
| `UsuarioController` | — | — | Si | `usuarios/` (3) | `Auth` + `Admin` (constructor) |

## 3. Relaciones verificadas frente a inferidas

| Relacion | Tipo | Como se determino |
|---|---|---|
| Aristas del mapa de dependencias | **Verificada** | Lectura de `require`, `use`, `extends` y `new` |
| `Core\Controller` → las 14 vistas | Verificada en forma, inferida en destino | El `require` es literal, la ruta se arma en ejecucion |
| `routes/web.php` → los 4 controladores | **Verificada** | `::class` + `new $clase()` en el Router |
| `$_SESSION['rol']` → `Middleware\Admin` | **Inferida por comportamiento** | Dependencia por dato compartido: ningun archivo nombra al otro |
| `$_SESSION['mensaje']` → `layouts/header.php` | **Inferida por comportamiento** | Los controladores escriben, la cabecera lee y borra |
| `productos.categoria` → `categorias.nombre` | **Verificada en el esquema** | `fk_producto_categoria` en `schema.sql` |
| `estilo.css` → clases de las vistas | **Inferida** | Correspondencia por nombre de clase CSS |

## 4. Observacion

Las dos relaciones a traves de `$_SESSION` son reales y criticas, pero **invisibles para
cualquier analisis estatico**: ningun archivo nombra al otro. Renombrar la clave `'rol'` en
`AuthController` sin cambiarla en `Middleware\Admin` no produciria ningun error visible, y
dejaria a todos los administradores fuera de `/usuarios`.

