# Guia del codigo

Recorrido por los archivos PHP: que hace cada clase, cada funcion y cada
metodo, y como se comunican.

## 1. `app/funciones.php` — 12 funciones, 4 grupos

### Enlaces
| Funcion | Recibe | Devuelve | Para que |
|---|---|---|---|
| `base_path()` | — | `""` o `"/subcarpeta"` | Detecta donde esta publicado el sitio |
| `url($ruta)` | ruta interna | URL completa con `index.php` | Enlaces y `action` de formularios |
| `asset($ruta)` | ruta de recurso | URL del archivo | CSS y fuentes |

### Seguridad
| Funcion | Recibe | Devuelve | Para que |
|---|---|---|---|
| `e($texto)` | texto o null | texto escapado | Anti-XSS al imprimir |
| `csrf_token()` | — | el token de la sesion | Lo crea la primera vez con `random_bytes(32)` |
| `csrf_campo()` | — | `<input type="hidden">` | Se pega en cada formulario |
| `verificar_csrf()` | — | nada, o corta con 403 | Compara con `hash_equals()` |

### Permisos
| Funcion | Que hace si falla |
|---|---|
| `proteger_sesion()` | Redirige a `/login` y corta |
| `proteger_admin()` | Deja aviso, redirige a `/productos` y corta |

### Datos y MVC
| Funcion | Recibe | Devuelve | Notas |
|---|---|---|---|
| `db()` | — | objeto `PDO` | `static $pdo` conserva la conexion entre llamadas |
| `vista($__viewPath,$datos)` | vista y datos | imprime HTML | `extract()` convierte claves en variables |
| `redirigir($ruta)` | ruta interna | corta con `exit` | Cabecera `Location` |

#### Por que el parametro de `vista()` se llama `$__viewPath`

```php
function vista(string $__viewPath, array $datos = []): void
{
    extract($datos);
    require __DIR__ . '/Views/' . $__viewPath . '.php';
}
```

`extract()` crea una variable por cada clave del arreglo. Si el parametro se
llamara `$nombre`, una llamada como

```php
vista('auth/registro', ['nombre' => '', 'apellido' => '', ...]);
```

haria que `extract()` **pisara el propio parametro** con el valor de
`$datos['nombre']` (cadena vacia), y el `require` siguiente intentaria abrir
`app/Views/.php`. Eso es exactamente lo que rompia `GET /registro` con un error
fatal: el codigo que llamaba era correcto, pero la variable se perdia dentro de
la funcion.

El guion bajo doble es deliberado: ningun formulario envia un campo llamado
`__viewPath`, asi que la colision se vuelve imposible. Es la unica variable del
proyecto con ese prefijo, y es por esta razon.

## 2. `app/Router.php` — clase `Router`

| Miembro | Tipo | Que hace |
|---|---|---|
| `$rutas` | propiedad privada | La tabla: metodo, patron, accion, permiso |
| `agregar($metodo,$patron,$accion,$permiso)` | publico | Guarda una ruta. No ejecuta nada |
| `despachar($metodo,$ruta)` | publico | Busca coincidencia, aplica permisos, llama al controlador. Si no hay, 404 |
| `partes($ruta)` | privado | `"/productos/5/editar"` -> `['productos','5','editar']` |
| `coincide($patron,$ruta)` | privado | Compara pedazo por pedazo. Devuelve el id, `null` o `false` |

`coincide()` devuelve tres cosas distintas y conviene tenerlo claro:
`false` = esta ruta no es; `null` = coincide y no hay id; un entero = coincide y
ese es el id. `{id}` solo acepta digitos (`ctype_digit`), lo que ademas **valida
el identificador** antes de que llegue al modelo.

## 3. Controladores

Todos siguen el mismo esquema: leen `$_POST`/`$_GET`, validan, llaman al
modelo, dejan un mensaje en `$_SESSION['mensaje']` y redirigen; o llaman a
`vista()`. Ninguno escribe SQL ni HTML. Ninguno controla permisos.

### `ProductoController`
| Metodo | Ruta | Que hace |
|---|---|---|
| `index()` | `GET /` y `/productos` | Listado, filtros y los datos del tablero |
| `crear()` | `GET /productos/crear` | Formulario vacio |
| `guardar()` | `POST /productos` | Valida y crea; si hay errores reimprime el formulario con lo escrito |
| `editar($id)` | `GET /productos/{id}/editar` | Formulario cargado |
| `actualizar($id)` | `POST /productos/{id}` | Valida y guarda cambios |
| `confirmarEliminar($id)` | `GET /productos/{id}/eliminar` | Pantalla de confirmacion |
| `eliminar($id)` | `POST /productos/{id}/eliminar` | Borra de verdad |
| `datosDelFormulario()` | privado | Junta y limpia los 5 campos |
| `paraGuardar($d)` | privado | Arma los marcadores `:nombre`, etc. Categoria vacia -> `null` (por la FK) |

### `CategoriaController`
`index()` lista; `guardar()` rechaza vacio y duplicado; `eliminar($id)` solo
borra si `Categoria::enUso()` devuelve 0.

### `AuthController`
| Metodo | Que hace |
|---|---|
| `login()` / `autenticar()` | Muestra el formulario / verifica con `password_verify()`, regenera el id de sesion y guarda `usuario_id`, `usuario`, `nombre`, `rol` |
| `registro()` / `registrar()` | Alta publica. El PRIMER usuario del sistema queda admin |
| `recuperar()` / `procesarRecuperar()` | Dos pasos: paso 1 muestra la pregunta; paso 2 valida la respuesta y cambia la clave |
| `logout()` | Vacia `$_SESSION` y `session_destroy()` |

La constante `PREGUNTAS` fija las cuatro preguntas posibles, y el registro
comprueba con `in_array(..., true)` que la enviada sea una de ellas.

### `UsuarioController`
`index()`, `crear()`, `guardar()`, `editar($id)`, `actualizar($id)`,
`resetearPassword($id)`, `eliminar($id)`, mas los privados
`datosDelFormulario()` y `validarPerfil()`.

Tres candados que **no son permisos sino reglas de negocio**:
1. No se puede quitar el rol admin al ultimo administrador.
2. No se puede eliminar al ultimo administrador.
3. Nadie puede eliminar su propia cuenta.

## 4. Modelos y sus consultas

### `Producto`
| Metodo | Consulta | Devuelve |
|---|---|---|
| `filtro()` (privado) | arma el `WHERE` | `[fragmento, parametros]` |
| `todos($buscar,$categoria)` | `SELECT ... FROM productos [WHERE] ORDER BY id` | filas |
| `porId($id)` | `SELECT * FROM productos WHERE id = :id` | fila o `null` |
| `metricas()` | `COUNT`, `SUM(stock)`, `SUM(precio*stock)`, `SUM(CASE WHEN stock<10)` | 4 numeros |
| `valorPorCategoria()` | `SUM(precio*stock) GROUP BY categoria` | grafico de barras |
| `stockPorCategoria()` | `SUM(stock) GROUP BY categoria` | grafico de pastel |
| `contarTodos()` | `SELECT COUNT(*)` | total sin filtro |
| `crear($d)` / `actualizar($id,$d)` / `eliminar($id)` | INSERT / UPDATE / DELETE | — / — / filas borradas |
| `validar($d)` | — | lista de errores |

**El punto clave de `filtro()`**: las condiciones las decide PHP, pero lo que
escribio el usuario **nunca** se pega al texto del SQL: viaja aparte en
`$params`. Ademas se escapan los comodines `%` y `_` del `LIKE` para que se
busquen como texto literal.

### `Categoria`
`todas()`, `nombres()` (solo nombres, para los desplegables), `existe($nombre)`,
`crear($nombre)`, `enUso($id)` (JOIN contra productos) y `eliminar($id)`.

### `Usuario`
`porUsuario()`, `existe()`, `datosRecuperacion()`, `actualizarPassword()`,
`todos()`, `porId()`, `crear()`, `actualizar()`, `actualizarPasswordPorId()`,
`eliminar()`, `contarAdmins()`, `contarTodos()`.

## 5. Validaciones

| Donde | Reglas |
|---|---|
| `Producto::validar()` | Nombre obligatorio; precio numerico y >= 0; stock numerico y >= 0 |
| Registro y alta de usuario | Nombre, apellido y usuario obligatorios; clave de 6+ caracteres; claves coincidentes; pregunta de la lista; respuesta obligatoria; usuario no repetido |
| `Router::coincide()` | El `{id}` de la URL tiene que ser numerico |
| `verificar_csrf()` | Todo POST tiene que traer el token correcto |

## 6. Vistas

Solo HTML y `echo` de datos ya preparados. Cada vista define `$titulo`,
opcionalmente `$navbar` y `$menuActivo`, e incluye `layouts/header.php` y
`layouts/footer.php`. Todo dato de la base se imprime con `e()`. Los
formularios POST llevan `csrf_campo()`. El unico `<form method="get">` es el de
filtros, que no modifica nada y por eso no lleva token.
