<?php
// =====================================================================
//  app/Core/autoload.php — El "bibliotecario" del proyecto.
//
//  Ubicacion en la arquitectura por modulos:
//    app/Core/autoload.php  <- se carga una sola vez desde public/index.php
//
//  Que problema resuelve: cuando el codigo menciona una clase
//  (por ejemplo App\Modules\Productos\Producto), PHP necesita saber
//  en que archivo esta definida. Sin este archivo habria que escribir
//  un require por cada clase en cada archivo que la use.
//
//  Como lo resuelve: con una regla unica. El namespace App\ corresponde
//  a la carpeta app/, por lo que:
//    App\Modules\Productos\Producto  ->  app/Modules/Productos/Producto.php
//    App\Core\Router                 ->  app/Core/Router.php
//    App\Helpers\funciones           ->  app/Helpers/funciones.php
//
//  Por que no Composer: para una regla de una linea no hace falta una
//  herramienta externa. Asi el proyecto no tiene dependencias y el
//  mecanismo completo es visible en este unico archivo.
//
//  Quien lo usa: public/index.php lo carga al arrancar. Despues trabaja
//  solo, sin que nadie mas lo llame explicitamente.
// =====================================================================

/**
 * Registra la funcion de autocarga de clases con PHP.
 *
 * spl_autoload_register() le entrega esta funcion anonima al motor de PHP.
 * PHP la invoca automaticamente cada vez que encuentra una clase que todavia
 * no esta en memoria, pasandole el nombre completo de la clase como argumento.
 *
 * Ejemplo paso a paso con App\Modules\Productos\Producto:
 *   1. Verificar que empieza con "App\"     -> si, continuamos
 *   2. Quitar el prefijo "App\"             -> "Modules\Productos\Producto"
 *   3. Reemplazar \ por /                   -> "Modules/Productos/Producto"
 *   4. Construir la ruta completa al archivo -> app/Modules/Productos/Producto.php
 *   5. Si el archivo existe, cargarlo con require
 *
 * @param string $clase Nombre completo de la clase con namespace
 *                      (ej. "App\Modules\Productos\ProductoController").
 * @return void
 */
spl_autoload_register(function (string $clase): void {
    // Prefijo comun a todas las clases de este proyecto
    $prefijo = 'App\\';

    // Si el nombre no empieza con App\, la clase pertenece a PHP o a una
    // extension (como PDO) y no debemos intervenir
    if (!str_starts_with($clase, $prefijo)) {
        return;
    }

    // Quitar el prefijo "App\" para quedarnos solo con la ruta relativa
    // Ej: "App\Modules\Productos\Producto" -> "Modules\Productos\Producto"
    $relativo = substr($clase, strlen($prefijo));

    // La raiz de la aplicacion es app/, un nivel arriba de este archivo (Core/)
    $raizApp = dirname(__DIR__); // apunta a app/

    // Convertir separadores de namespace (\) a separadores de ruta (/)
    // y agregar la extension .php
    $ruta = $raizApp . '/' . str_replace('\\', '/', $relativo) . '.php';

    // Cargar el archivo solo si existe (evita errores fatales por typos)
    if (is_file($ruta)) {
        require $ruta;
    }
});
