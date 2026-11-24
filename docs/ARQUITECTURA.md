# Arquitectura — Tarea CRUD PHP (Sistema de inventario)

Aplicacion de inventario en **PHP nativo**, sin framework, con patron **MVC
simplificado** y entorno **Docker**.

> **Diagramas**: `DOCUMENTACION/recursos/diagramas/01-arquitectura-general.png`
> (las tres capas), `02-flujo-peticion.png` (el recorrido paso a paso) y
> `03-mvc-proyecto.png` (las tres capas del MVC con los archivos reales).

## 1. Idea general

Toda peticion entra por un unico archivo (`public/index.php`), que decide quien
responde. Nada mas es alcanzable desde el navegador.

```
NAVEGADOR
    |
    v
public/index.php          FRONT CONTROLLER
    |   1. session_start()
    |   2. carga app/autoload.php y app/funciones.php
    |   3. si es POST -> verificar_csrf()
    |   4. carga routes/web.php en el Router
    v
app/Router.php            ENRUTADOR
    |   busca la ruta que coincide
    |   aplica el permiso declarado (sesion / admin)
    v
app/Controllers/...       CONTROLADOR
    |   lee $_POST / $_GET, valida
    |   pide datos al modelo
    v
app/Models/...            MODELO  ---- PDO ----> MySQL
    |   devuelve arreglos
    v
vista('productos/index')  VISTA
    |   solo HTML + echo de datos escapados con e()
    v
HTML al NAVEGADOR
```

## 2. Responsabilidad de cada componente

| Componente | Responsabilidad | Lo que NO hace |
|---|---|---|
| `public/index.php` | Arrancar sesion, cargar ayudas, verificar CSRF, despachar | No tiene logica de negocio |
| `app/autoload.php` | Traducir `App\Models\Producto` a `app/Models/Producto.php` | No carga clases ajenas al namespace `App\` |
| `app/funciones.php` | Enlaces, escape, CSRF, permisos, conexion, `vista()`, `redirigir()` | No consulta la base ni arma HTML |
| `app/Router.php` | Guardar la tabla de rutas y elegir el controlador | No valida formularios ni toca la base |
| `app/Controllers/` | Recibir la peticion, validar, coordinar, elegir vista | No escribe SQL ni HTML |
| `app/Models/` | Todas las consultas SQL con PDO preparado | No imprime nada |
| `app/Views/` | Mostrar informacion | No consulta la base |
| `config/config.php` | Decir de donde salen las credenciales | No contiene secretos |
| `routes/web.php` | Declarar direcciones y permisos | No ejecuta nada |

## 3. Por que este MVC y no otro

- **Sin framework**: el proyecto es academico y el objetivo es entender cada
  pieza. Un framework esconde justo lo que hay que aprender.
- **Front controller unico**: deja `app/`, `config/` y `secrets.php` fuera de la
  carpeta servida por Apache. Es seguridad estructural, no una regla que se
  pueda olvidar.
- **Funciones sueltas en vez de clases de servicio**: `url()`, `e()`, `db()`,
  `vista()`, `redirigir()` y los dos permisos no guardan estado. Una clase con
  metodos estaticos haria lo mismo escribiendo mas.
- **El Router si es clase**: necesita guardar la tabla de rutas entre
  `agregar()` y `despachar()`. Ese estado compartido justifica la clase.
- **Modelos con metodos estaticos**: `Producto::todos()` se lee directo y no
  hay nada que guardar en una instancia.

## 4. Permisos: una sola fuente de verdad

Los permisos se declaran **solo** en `routes/web.php`, con el cuarto parametro.
La tabla tiene **25 rutas** en total:

| Valor | Significado | Rutas |
|---|---|---|
| (nada) | publico: `/login`, `/registro`, `/recuperar`, `/logout` | 7 |
| `'sesion'` | hay que estar logueado: productos y categorias | 11 |
| `'admin'` | sesion + rol admin: las rutas de `/usuarios` | 7 |

El Router ejecuta `proteger_sesion()` y, si corresponde, `proteger_admin()`
antes de instanciar el controlador. Ningun controlador controla permisos por su
cuenta. Ver `DOCUMENTACION/recursos/diagramas/06-permisos-rutas.png`.

Detalle que conviene no confundir: **categorias requiere `'sesion'`, no
`'admin'`**. Cualquier usuario autenticado puede crear y borrar categorias; lo
que esta reservado al administrador es unicamente la gestion de usuarios.

## 5. Flujo completo de ejemplo: editar el producto 5

1. El navegador pide `GET /index.php/productos/5/editar`.
2. `index.php` abre sesion, carga ayudas. No es POST: no hay CSRF que verificar.
3. El Router parte la direccion en `['productos','5','editar']` y la compara con
   cada patron. Coincide con `/productos/{id}/editar`; `5` son digitos, asi que
   el id vale 5.
4. La ruta pide permiso `'sesion'`: corre `proteger_sesion()`. Si no hay sesion,
   redirige al login y termina.
5. `new ProductoController()` y se llama `editar(5)`.
6. El controlador pide `Producto::porId(5)` y `Categoria::nombres()`.
7. El modelo ejecuta `SELECT * FROM productos WHERE id = :id` con PDO preparado.
8. El controlador llama `vista('productos/editar', [...])`.
9. `extract()` convierte las claves en variables y se incluye la vista.
10. La vista imprime el formulario, escapando con `e()` e incluyendo
    `csrf_campo()`.

## 6. Comunicacion entre archivos

- `index.php` -> `autoload.php`, `funciones.php`, `routes/web.php`, `Router.php`
- `Router.php` -> `proteger_sesion()` / `proteger_admin()` (de `funciones.php`) y los controladores
- Controladores -> modelos, `vista()`, `redirigir()`
- Modelos -> `db()` -> `config/config.php` -> variables de entorno o `secrets.php`
- Vistas -> `url()`, `asset()`, `e()`, `csrf_campo()` y los layouts
