<?php
/**
 * public/index.php — FRONT CONTROLLER (único punto de entrada)
 *
 * TODA petición HTTP del sitio pasa por aquí sin excepción.
 * El servidor web (Apache + mod_rewrite / .htaccess) redirige
 * cada URL hacia este archivo.
 *
 * Por qué una sola puerta de entrada:
 *   1. SEGURIDAD: solo la carpeta public/ es accesible desde el
 *      navegador. El código fuente (app/), la configuración (config/)
 *      y los secretos quedan fuera del alcance de cualquier URL.
 *   2. PUNTO CENTRAL: todo lo que ocurre en cada request pasa aquí:
 *      iniciar sesión, cargar helpers, verificar CSRF, despachar ruta.
 *      No hay nada escondido en un archivo paralelo.
 *
 * Flujo de ejecución (en orden):
 *   1. Iniciar la sesión PHP.
 *   2. Cargar el autoloader (encuentra clases por namespace).
 *   3. Cargar las funciones de ayuda (url, e, db, vista, csrf...).
 *   4. Verificar el token CSRF en peticiones POST.
 *   5. Instanciar el Router y registrar todas las rutas.
 *   6. Extraer la ruta pedida y despacharla al controlador correcto.
 */

// 1. La sesión debe iniciarse ANTES de cualquier salida al navegador.
//    Aquí viven los mensajes flash, el usuario logueado y el token CSRF.
session_start();

// 2. El autoloader mapea namespaces a rutas de archivo.
//    Ejemplo: App\Modules\Productos\ProductoController
//          -> app/Modules/Productos/ProductoController.php
//    Ubicación nueva: app/Core/autoload.php (antes: app/autoload.php)
require __DIR__ . '/../app/Core/autoload.php';

// 3. Funciones de ayuda globales reutilizadas en controladores y vistas:
//    url()       -> genera URLs relativas al front controller
//    asset()     -> genera rutas a archivos estáticos (css, js, img)
//    e()         -> htmlspecialchars() para evitar XSS en vistas
//    db()        -> devuelve la conexión PDO compartida
//    vista()     -> incluye una vista de módulo con sus datos
//    csrf_campo()   -> imprime el campo oculto con el token CSRF
//    verificar_csrf() -> valida el token; aborta si no coincide
//    permiso()   -> verifica que el usuario tenga el rol requerido
//    Ubicación nueva: app/Helpers/funciones.php (antes: app/funciones.php)
require __DIR__ . '/../app/Helpers/funciones.php';

// 4. PROTECCIÓN CSRF — verificada aquí para TODOS los POST del sitio.
//    Centralizar esta verificación es fundamental: si cada controlador
//    tuviera que acordarse de llamarla, bastaría olvidarse una vez
//    para dejar una brecha de seguridad. Aquí es imposible saltársela.
//    Los GET no se verifican porque no modifican datos.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
}

// 5. El Router vive ahora en App\Core\Router (antes: App\Router).
//    Se instancia vacío y routes/web.php le inyecta todas las rutas.
//    require devuelve la función anónima definida en ese archivo;
//    la guardamos en $registrarRutas y la llamamos a continuación.
$router = new App\Core\Router();
$registrarRutas = require __DIR__ . '/../routes/web.php';
$registrarRutas($router);

// 6. PATH_INFO contiene la parte de la URL después de index.php.
//    Ejemplos:
//      /productos/crear  ->  PATH_INFO = /productos/crear
//      /                 ->  PATH_INFO no existe, se usa '/'
//    trim() elimina barras dobles al inicio/final antes de despachar.
$ruta = $_SERVER['PATH_INFO'] ?? '/';
$ruta = '/' . trim($ruta, '/');

$router->despachar($_SERVER['REQUEST_METHOD'], $ruta);
