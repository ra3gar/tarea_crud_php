<?php
// =====================================================================
//  app/Helpers/funciones.php — Las ayudas cortas que usa TODO el proyecto.
//
//  Ubicacion en la arquitectura por modulos:
//    app/Helpers/  <- funciones compartidas entre todos los modulos
//    app/Core/     <- motor del framework (Router, autoload)
//    app/Modules/  <- cada modulo con su Controlador, Modelo y Vistas
//
//  Que es: un solo archivo con funciones sueltas y directas. No es una
//  clase porque ninguna de estas ayudas necesita guardar estado ni
//  heredar nada: son calculos de una linea o muy cortos.
//
//  Por que existe: para no repetir en veinte archivos el mismo codigo de
//  armar un enlace, escapar texto o pedir la conexion a la base.
//
//  Quien lo usa: public/index.php lo carga UNA vez al arrancar, y desde
//  ahi queda disponible en controladores, modelos y vistas.
//
//  Que contiene, en cuatro grupos:
//    1. Enlaces  : base_path(), url(), asset()
//    2. Seguridad: e(), csrf_token(), csrf_campo(), verificar_csrf()
//    3. Permisos : proteger_sesion(), proteger_admin()
//    4. Datos/MVC: db(), vista(), redirigir()
// =====================================================================


// ---------------------------------------------------------------------
//  1. ENLACES
// ---------------------------------------------------------------------

/**
 * Carpeta base desde la que el navegador accede a index.php.
 *
 * Por que existe: el proyecto puede estar publicado en la raiz del sitio
 * o dentro de una subcarpeta. Si los enlaces se escribieran fijos ("/productos"),
 * se romperian al moverlo. Esta funcion lo detecta automaticamente en cada peticion.
 *
 * Como funciona: lee SCRIPT_NAME (lo pone el servidor web) para saber donde
 * vive index.php, y descarta el nombre del script para quedarse solo con
 * la carpeta contenedora.
 *
 * @return string Ruta base SIN barra final.
 *                Ejemplo: ""  si el sitio esta en la raiz
 *                         "/inventario"  si esta en una subcarpeta
 */
function base_path(): string
{
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
}

/**
 * Construye un enlace hacia una ruta interna de la aplicacion.
 *
 * Por que existe: toda la navegacion pasa por index.php (patron front controller),
 * asi que cada enlace debe incluirlo. Esta funcion lo hace en un solo lugar
 * para que si la estructura cambia, baste con cambiarla aqui.
 *
 * @param string $ruta Ruta interna sin barra inicial (ej. "productos/crear").
 * @return string URL completa lista para usar en href o en action de un formulario.
 *
 * Ejemplo: url('productos') -> "/index.php/productos"
 */
function url(string $ruta = ''): string
{
    return base_path() . '/index.php/' . ltrim($ruta, '/');
}

/**
 * Construye la URL de un archivo estatico ubicado en public/ (css, fuentes, etc.).
 *
 * @param string $ruta Ruta del recurso relativa a public/ (ej. "assets/css/estilo.css").
 * @return string URL completa del archivo estatico.
 */
function asset(string $ruta): string
{
    return base_path() . '/' . ltrim($ruta, '/');
}


// ---------------------------------------------------------------------
//  2. SEGURIDAD DE LO QUE SE MUESTRA Y DE LO QUE SE ENVIA
// ---------------------------------------------------------------------

/**
 * Escapa texto antes de imprimirlo en una vista. Defensa contra XSS.
 *
 * Que problema resuelve: si un producto se llamara <script>alert(1)</script>
 * y lo imprimieramos tal cual, el navegador ejecutaria ese codigo JavaScript.
 * Al escaparlo, el navegador lo muestra como texto plano y no lo ejecuta.
 *
 * ENT_QUOTES escapa tanto las comillas simples como las dobles, necesario
 * cuando el texto va dentro de un atributo HTML (value="...").
 *
 * @param string|null $texto Texto a escapar (acepta null y lo trata como "").
 * @return string Texto seguro para insertar dentro de HTML.
 */
function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES);
}

/**
 * Devuelve el token CSRF de esta sesion, creandolo la primera vez.
 *
 * Que es CSRF: un sitio malicioso puede tener un formulario escondido que
 * apunte a nuestro sistema. Si el usuario tiene la sesion abierta y visita
 * ese sitio, el navegador envia automaticamente su cookie de sesion y la
 * operacion se ejecuta como si la hubiera pedido el mismo.
 *
 * Como lo evita el token: guardamos una cadena secreta en la sesion y la
 * copiamos en cada formulario. El sitio atacante no puede leer la sesion,
 * asi que su formulario llega sin token y lo rechazamos.
 *
 * random_bytes() genera bytes aleatorios de calidad criptografica.
 * bin2hex() los convierte a caracteres legibles para incrustarlos en HTML.
 *
 * @return string El token, siempre el mismo durante toda la sesion activa.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Genera el campo oculto HTML con el token CSRF, listo para pegar en un form.
 *
 * El campo es invisible para el usuario pero el servidor lo valida al recibir
 * el formulario con verificar_csrf().
 *
 * @return string HTML del input oculto con el token.
 *
 * Ejemplo de uso dentro de un formulario PHP:
 *   <form method="post">
 *     <?= csrf_campo() ?>
 *     ...
 *   </form>
 */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verifica que el POST recibido traiga el token CSRF correcto.
 *
 * Donde se usa: en public/index.php, una sola vez, para TODAS las peticiones
 * POST. Asi ningun formulario puede quedar sin proteccion por descuido.
 *
 * hash_equals() compara ambas cadenas en tiempo constante (siempre tarda lo
 * mismo sin importar donde falla la comparacion). Eso evita ataques de
 * "timing" donde un atacante mide cuanto tarda la comparacion para adivinar
 * el token caracter por caracter.
 *
 * @return void Si el token es valido, no hace nada y la ejecucion continua.
 *              Si es invalido, responde HTTP 403 y detiene el script.
 */
function verificar_csrf(): void
{
    $enviado  = $_POST['csrf_token'] ?? '';
    $esperado = $_SESSION['csrf_token'] ?? '';
    if ($esperado === '' || !is_string($enviado) || !hash_equals($esperado, $enviado)) {
        http_response_code(403);
        echo 'La solicitud no pudo verificarse. Volve a cargar la pagina e intenta de nuevo.';
        exit;
    }
}


// ---------------------------------------------------------------------
//  3. PERMISOS DE ACCESO
// ---------------------------------------------------------------------

/**
 * Exige que haya una sesion iniciada. Si no la hay, redirige al login.
 *
 * Quien la llama: el Router (app/Core/Router.php) cuando la ruta fue
 * registrada con permiso 'sesion' o 'admin' en routes/web.php.
 * Los controladores no necesitan llamarla directamente.
 *
 * @return void Si hay sesion activa, no hace nada.
 *              Si no hay sesion, redirige a /login y detiene el script.
 */
function proteger_sesion(): void
{
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . url('login'));
        exit;
    }
}

/**
 * Exige que el usuario de la sesion tenga rol de administrador.
 *
 * Siempre se ejecuta DESPUES de proteger_sesion(): primero se comprueba
 * que exista una sesion, luego que el rol sea el correcto.
 *
 * @return void Si el usuario es admin, no hace nada.
 *              Si no lo es, deja un aviso flash, redirige a Productos y detiene el script.
 */
function proteger_admin(): void
{
    if (($_SESSION['rol'] ?? '') !== 'admin') {
        $_SESSION['mensaje'] = ['texto' => 'Solo un administrador puede gestionar usuarios.', 'tipo' => 'error'];
        header('Location: ' . url('productos'));
        exit;
    }
}


// ---------------------------------------------------------------------
//  4. BASE DE DATOS Y PIEZAS DEL MVC
// ---------------------------------------------------------------------

/**
 * Devuelve la conexion PDO a MySQL, creandola solo la primera vez que se pide.
 *
 * Implementa el patron Singleton de forma simple usando una variable estatica
 * local: la primera llamada crea el objeto PDO; las llamadas siguientes
 * devuelven el mismo objeto ya creado, sin abrir una nueva conexion.
 *
 * Por que no una clase Database: una funcion con 'static' hace exactamente
 * lo mismo y es mas facil de leer sin necesitar conocer el patron Singleton.
 *
 * Opciones de configuracion de la conexion:
 *   ERRMODE_EXCEPTION  -> un error de SQL lanza una excepcion PHP en vez de
 *                         pasar desapercibido (facilita depuracion)
 *   FETCH_ASSOC        -> las filas llegan como arreglos con nombre de columna,
 *                         no como listas numeradas
 *   EMULATE_PREPARES   -> false: las consultas preparadas son REALES en el
 *                         servidor MySQL, no simuladas por PHP. Es la defensa
 *                         de fondo contra inyeccion SQL.
 *
 * @return PDO Objeto de conexion listo para prepare() y execute().
 */
function db(): PDO
{
    // $pdo conserva su valor entre llamadas gracias a 'static'
    static $pdo = null;

    if ($pdo === null) {
        // Las credenciales vienen de config/config.php, que las lee de
        // variables de entorno (Docker) o de secrets.php
        $cfg = require __DIR__ . '/../../config/config.php';
        $dsn = "mysql:host={$cfg['host']};dbname={$cfg['nombre']};charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $cfg['usuario'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // No se muestra el detalle al usuario (podria revelar datos internos)
            // El error real va al log del servidor web
            error_log('Error de conexion a la base de datos: ' . $e->getMessage());
            http_response_code(500);
            echo 'No se pudo conectar con la base de datos. Avisa al administrador.';
            exit;
        }
    }

    return $pdo;
}

/**
 * Carga una vista de un modulo y le inyecta los datos que debe mostrar.
 *
 * Esta funcion es la unica puerta por la que un controlador llega al HTML.
 * Los controladores no hacen echo directamente; llaman a vista() y le pasan
 * un arreglo con los datos necesarios.
 *
 * Como funciona extract(): convierte las claves del arreglo en variables PHP.
 * Si le pasas ['productos' => [...]], dentro de la vista existe $productos.
 * Es la forma de pasar datos a la vista sin usar variables globales.
 *
 * Resolucion de ruta:
 *   - El primer segmento de $__viewPath debe ser el nombre del modulo con
 *     mayuscula inicial (ej. "Productos/index", "Auth/login").
 *   - Las rutas de layouts compartidos se resuelven desde app/Views/layouts/.
 *
 * @param string $__viewPath Ruta relativa a app/Modules/, sin extension .php.
 *                           Ejemplos: "Productos/index", "Usuarios/crear", "Auth/login"
 * @param array  $datos      Variables que la vista necesita, indexadas por nombre.
 * @return void              Imprime el HTML directamente.
 */
function vista(string $__viewPath, array $datos = []): void
{
    // Convertir las claves del arreglo en variables locales de esta funcion.
    // Despues del extract(), si $datos tenia 'productos', aqui existe $productos.
    extract($datos);

    // Ruta completa al archivo de vista dentro de app/Vistas/
    $__archivo = dirname(__DIR__) . '/Vistas/' . strtolower($__viewPath) . '.php';

    require $__archivo;
}

/**
 * Redirige el navegador a otra ruta interna de la aplicacion y detiene el script.
 *
 * Por que el exit: despues de enviar la cabecera Location, cualquier codigo
 * que siguiera ejecutandose podria escribir en la base de datos o imprimir
 * HTML sin que el usuario lo vea. El exit lo evita.
 *
 * @param string $ruta Ruta interna destino sin barra inicial (ej. "productos").
 * @return void        Envia la cabecera HTTP y termina la ejecucion.
 */
function redirigir(string $ruta): void
{
    header('Location: ' . url($ruta));
    exit;
}
