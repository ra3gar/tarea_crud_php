# Seguridad

Cada medida, que problema evita y donde esta implementada.

## 1. Inyeccion SQL — PDO con consultas preparadas

**El problema**: si el SQL se armara pegando texto del usuario
(`"... WHERE nombre = '$nombre'"`), alguien podria escribir `' OR 1=1 --` y
cambiar el significado de la consulta.

**La defensa**: todas las consultas usan `prepare()` con marcadores (`:nombre`)
y los valores viajan aparte en `execute()`. MySQL recibe la estructura y los
datos por separado, asi que un dato nunca puede convertirse en instruccion.

Ademas, en `db()`: `PDO::ATTR_EMULATE_PREPARES => false`. Sin eso, PHP simula
las consultas preparadas armando el texto el mismo. Con `false`, las prepara de
verdad el servidor MySQL.

**Donde**: los tres modelos, todas sus consultas. En `Producto::filtro()` el
`WHERE` se arma en PHP pero **los valores siempre van como parametros**, y los
comodines `%` y `_` del `LIKE` se escapan para que se busquen literales.

## 2. XSS — escape de todo lo que se imprime

**El problema**: un producto llamado `<script>alert(1)</script>`, impreso tal
cual, se ejecutaria en el navegador de quien mire la lista.

**La defensa**: `e()` envuelve `htmlspecialchars(..., ENT_QUOTES)`. `ENT_QUOTES`
tambien escapa comillas, necesario cuando el texto va dentro de un atributo
(`value="..."`).

**Donde**: `app/funciones.php` y todas las vistas.

## 3. CSRF — token por sesion (NUEVO en esta version)

**El problema**: un sitio malicioso puede tener un formulario escondido que
apunte a este sistema. Si el usuario tiene la sesion abierta y visita ese sitio,
el navegador envia la cookie de sesion y la operacion (borrar un producto,
resetear una clave) se ejecuta como si la hubiera pedido el usuario.

**La defensa**: se guarda una cadena secreta en la sesion y se copia en cada
formulario. El sitio atacante no puede leerla, asi que su formulario llega sin
token y se rechaza.

**Como esta hecho**:
- `csrf_token()` genera el token con `bin2hex(random_bytes(32))`, aleatorio
  criptografico, una vez por sesion.
- `csrf_campo()` imprime el `<input type="hidden">`. Es invisible: no cambia
  nada de la pantalla.
- `verificar_csrf()` compara con `hash_equals()`, que tarda siempre lo mismo.
  Con `==`, un atacante podria deducir el token midiendo cuanto tarda la
  comparacion en fallar.
- La verificacion esta en **`public/index.php`, una sola vez, para TODOS los
  POST**. Por que ahi y no en cada controlador: si cada formulario tuviera que
  acordarse de verificar, alcanzaria olvidarse una vez para dejar un agujero.
- Si falla: **403** con mensaje generico, sin detalles internos.
- Los GET no se verifican: no modifican informacion.

**Cobertura**: los **13** formularios POST del sistema llevan `csrf_campo()`
(verificado: 13 formularios `method="post"`, 13 llamadas a `csrf_campo()`). El
unico `<form method="get">` (filtros del listado) no lleva token porque no
modifica nada.

## 4. Contraseñas

| Medida | Donde |
|---|---|
| `password_hash($clave, PASSWORD_DEFAULT)` al crear y al resetear | `AuthController`, `UsuarioController` |
| `password_verify()` al entrar | `AuthController::autenticar()` |
| La **respuesta de seguridad tambien se hashea** | registro y alta de usuarios |
| Nunca se muestra ni se devuelve una clave | todo el sistema |

El hash es de una sola via: aunque alguien leyera la tabla `usuarios`, no
obtiene las contraseñas. La respuesta de seguridad se hashea por lo mismo: es
una credencial, no un dato mas. Se normaliza con `mb_strtolower()` antes de
hashear, para que la comparacion no dependa de mayusculas.

## 5. Sesiones

| Medida | Por que |
|---|---|
| `session_regenerate_id(true)` al iniciar sesion | Evita **fijacion de sesion**: si un atacante consiguio fijar un id antes del login, ese id deja de servir |
| `$_SESSION = []` + `session_destroy()` al salir | Cierre completo, no solo olvidar el usuario |
| En la sesion se guarda `usuario_id`, `usuario`, `nombre`, `rol` | Lo minimo necesario |

## 6. Rutas privadas y permisos por rol

- `proteger_sesion()` exige sesion iniciada.
- `proteger_admin()` exige rol `admin`.
- Los dos los ejecuta el **Router**, segun el permiso declarado en
  `routes/web.php`. Primero sesion, despues rol.
- **Una sola fuente de verdad**: no hay permisos escondidos en controladores.
  En la version anterior, el permiso de admin vivia en el constructor de
  `UsuarioController` y no se veia en la tabla de rutas.

De las **25 rutas**, 7 son publicas, 11 exigen sesion y 7 exigen admin. Las 7 de
`'admin'` son exactamente las de `/usuarios`: un usuario normal que escriba la
URL a mano recibe un aviso y vuelve a Productos. Ver
`DOCUMENTACION/recursos/diagramas/06-permisos-rutas.png`.

Conviene no confundirse: **categorias NO es solo de admin**. Requiere `'sesion'`,
igual que productos.

## 6-bis. Candados de negocio sobre los usuarios

No son permisos (no dependen de quien sos) sino reglas que protegen al sistema
de quedar inutilizable. Viven en `UsuarioController`:

| Candado | Donde | Que evita |
|---|---|---|
| No quitar el rol admin al ultimo administrador | `actualizar()` | Que el sistema quede sin nadie que pueda gestionar usuarios |
| No eliminar al ultimo administrador | `eliminar()` | Lo mismo, por la via del borrado |
| Nadie puede eliminar su propia cuenta | `eliminar()` | Que un admin se expulse a si mismo por error |

Los dos primeros se apoyan en `Usuario::contarAdmins() <= 1`.

## 7. Validacion de identificadores

`Router::coincide()` acepta `{id}` **solo si son digitos** (`ctype_digit`) y lo
convierte a entero. Un id invalido da 404 y nunca llega al modelo.

### Validacion de formularios en dos capas

Los formularios de producto validan **dos veces**, y las dos capas cumplen
funciones distintas:

| Capa | Donde | Para que sirve |
|---|---|---|
| Navegador | `required`, `type="number"`, `min="0"` en `Views/productos/_campos.php` | Comodidad: avisa al instante, sin ida y vuelta al servidor |
| Servidor | `Producto::validar()` | **La defensa real**: nombre no vacio, precio y stock numericos y `>= 0` |

La capa del navegador se puede saltar (basta con desactivar JavaScript, usar
`curl` o editar el HTML). Por eso la del servidor no es opcional ni redundante:
es la unica que de verdad protege los datos. La del navegador es una cortesia
para el usuario, no una medida de seguridad.

## 8. Mensajes de error que no revelan informacion

| Situacion | Que ve el usuario |
|---|---|
| Usuario o clave incorrectos | "Usuario o contrasena incorrectos." — no dice cual de los dos fallo |
| Fallo de conexion a la base | Mensaje generico + 500. El detalle va a `error_log()`, no a la pantalla |
| Token CSRF invalido | "La solicitud no pudo verificarse." + 403 |

Decir "ese usuario no existe" le confirmaria a un atacante que el otro usuario
si existe.

## 9. Proteccion de archivos en el servidor

**Primera linea — estructural**: Apache sirve solo `public/`
(`docker/apache.conf`). `app/`, `config/`, `secrets.php`, `database/` y
`docs/` **no estan** en la carpeta servida. No es una regla que se pueda
desactivar: no estan en el camino.

**Segunda linea — por si acaso** (`apache-deny.conf`, cargado en Apache):
- todo archivo que empiece con `.` (incluye `.env`)
- `*.env`, `*.sql`, `*.md`
- `Dockerfile`, `docker-compose.yml`, `secrets.php`, `apache-deny.conf`

## 10. Credenciales fuera del repositorio

- `config/config.php` **no contiene secretos**: decide la fuente y devuelve un
  arreglo. Primero variables de entorno (las inyecta Docker), si no
  `secrets.php`.
- `.env` y `secrets.php` estan en `.gitignore`. Se versionan solo las
  plantillas `.env.example` y `secrets.example.php`.
- Ninguna clave aparece en el codigo ni en esta documentacion.

## 11. Lo que este proyecto NO implementa

Por honestidad, y porque son decisiones conscientes para un proyecto academico:

- **Sin limite de intentos de login** (rate limiting): nada frena un ataque de
  fuerza bruta contra `/login`.
- **Sin HTTPS**: el entorno es local por HTTP, asi que las claves viajan en
  claro por la red local.
- **Sin atributos de cookie endurecidos** (`Secure`, `SameSite=Strict`,
  `HttpOnly` explicito): se usan los valores por defecto de PHP.
- **Sin caducidad de sesion por inactividad**.
- **Registro publico abierto**: cualquiera puede crear una cuenta de usuario
  normal.
