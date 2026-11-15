<?php
// =====================================================================
//  public/index.php — EL FRONT CONTROLLER. La unica puerta de entrada.
//  TODA peticion del sitio pasa por aca. Su trabajo es corto:
//    1. Iniciar la sesion.
//    2. Encender el autoloader y los ayudantes.
//    3. Cargar la tabla de rutas.
//    4. Pasarle al Router la direccion pedida para que la despache.
//  Es lo unico que el navegador alcanza directamente; todo lo demas
//  (app/, config/, secrets) vive afuera de esta carpeta.
// =====================================================================
session_start();

require __DIR__ . '/../app/Core/autoload.php';
require __DIR__ . '/../app/Core/helpers.php';

$router = new App\Core\Router();
(require __DIR__ . '/../routes/web.php')($router);

// La direccion pedida viaja en PATH_INFO: la parte que va despues de
// index.php. Ej: index.php/productos/crear  ->  /productos/crear
$ruta = $_SERVER['PATH_INFO'] ?? '/';
$ruta = '/' . trim($ruta, '/');

$router->despachar($_SERVER['REQUEST_METHOD'], $ruta);
