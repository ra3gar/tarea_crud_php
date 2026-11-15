<?php
// =====================================================================
//  routes/web.php — Todas las rutas del sitio, en un solo lugar legible.
//  Cada linea: metodo + direccion -> [Controlador, metodo] + si es privada.
//  El ultimo parametro true = exige sesion (lo revisa el Middleware Auth).
// =====================================================================

use App\Controllers\ProductoController;
use App\Controllers\CategoriaController;
use App\Controllers\AuthController;
use App\Controllers\UsuarioController;

return function ($router) {
    // --- Autenticacion (publico) ---
    $router->agregar('GET',  '/login',     [AuthController::class, 'login']);
    $router->agregar('POST', '/login',     [AuthController::class, 'autenticar']);
    $router->agregar('GET',  '/registro',  [AuthController::class, 'registro']);
    $router->agregar('POST', '/registro',  [AuthController::class, 'registrar']);
    $router->agregar('GET',  '/recuperar', [AuthController::class, 'recuperar']);
    $router->agregar('POST', '/recuperar', [AuthController::class, 'procesarRecuperar']);
    $router->agregar('GET',  '/logout',    [AuthController::class, 'logout']);

    // --- Productos (privado: requiere sesion) ---
    $router->agregar('GET',  '/',                        [ProductoController::class, 'index'], true);
    $router->agregar('GET',  '/productos',               [ProductoController::class, 'index'], true);
    $router->agregar('GET',  '/productos/crear',         [ProductoController::class, 'crear'], true);
    $router->agregar('POST', '/productos',               [ProductoController::class, 'guardar'], true);
    $router->agregar('GET',  '/productos/{id}/editar',   [ProductoController::class, 'editar'], true);
    $router->agregar('POST', '/productos/{id}',          [ProductoController::class, 'actualizar'], true);
    $router->agregar('GET',  '/productos/{id}/eliminar', [ProductoController::class, 'confirmarEliminar'], true);
    $router->agregar('POST', '/productos/{id}/eliminar', [ProductoController::class, 'eliminar'], true);

    // --- Categorias (privado) ---
    $router->agregar('GET',  '/categorias',               [CategoriaController::class, 'index'], true);
    $router->agregar('POST', '/categorias',               [CategoriaController::class, 'guardar'], true);
    $router->agregar('POST', '/categorias/{id}/eliminar', [CategoriaController::class, 'eliminar'], true);

    // --- Usuarios (privado + SOLO admin: el guardia Admin lo revisa adentro) ---
    $router->agregar('GET',  '/usuarios',               [UsuarioController::class, 'index'], true);
    $router->agregar('GET',  '/usuarios/crear',         [UsuarioController::class, 'crear'], true);
    $router->agregar('POST', '/usuarios',               [UsuarioController::class, 'guardar'], true);
    $router->agregar('GET',  '/usuarios/{id}/editar',   [UsuarioController::class, 'editar'], true);
    $router->agregar('POST', '/usuarios/{id}',          [UsuarioController::class, 'actualizar'], true);
    $router->agregar('POST', '/usuarios/{id}/password', [UsuarioController::class, 'resetearPassword'], true);
    $router->agregar('POST', '/usuarios/{id}/eliminar', [UsuarioController::class, 'eliminar'], true);
};
