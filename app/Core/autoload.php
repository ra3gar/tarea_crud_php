<?php
// =====================================================================
//  app/Core/autoload.php — El "bibliotecario" del proyecto.
//
//  Cuando el codigo pide una clase (por ejemplo App\Core\Router), PHP
//  necesita saber en que archivo esta. Esta funcion se lo dice sola:
//  el namespace App\ corresponde a la carpeta app/, asi que
//  App\Models\Producto se carga desde app/Models/Producto.php.
//
//  Son estas pocas lineas. No hace falta Composer ni ninguna herramienta
//  externa: todo el "autoloader" esta a la vista aca.
// =====================================================================

/**
 * Autoloader: traduce un nombre de clase a la ruta de su archivo y lo carga.
 *
 * Se registra con spl_autoload_register, asi PHP la llama solo cada vez que
 * usa una clase que todavia no esta cargada en memoria.
 *
 * @param string $clase Nombre completo de la clase con namespace
 *                      (ej. "App\\Models\\Producto").
 * @return void         Incluye el archivo si existe; si la clase no pertenece
 *                      al namespace App\\, no hace nada.
 */
spl_autoload_register(function (string $clase): void {
    $prefijo = 'App\\';
    // Si la clase no empieza con App\, no es nuestra: la ignoramos.
    if (strncmp($clase, $prefijo, strlen($prefijo)) !== 0) {
        return;
    }
    // App\Models\Producto  ->  Models/Producto  ->  app/Models/Producto.php
    $relativo = substr($clase, strlen($prefijo));
    $ruta = __DIR__ . '/../' . str_replace('\\', '/', $relativo) . '.php';
    if (is_file($ruta)) {
        require $ruta;
    }
});
