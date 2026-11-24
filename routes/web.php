<?php
/**
 * routes/web.php — TABLA DE RUTAS del sistema
 *
 * Este archivo define TODAS las URLs que el sitio conoce y quién
 * responde a cada una. Es el índice completo de acceso al sistema.
 *
 * Por qué un archivo aparte (y no inline en index.php):
 *   - Legibilidad: una sola lectura revela todas las rutas, permisos
 *     y controladores. No hay que buscar en cada archivo de clase.
 *   - Única fuente de verdad para permisos: el cuarto parámetro de
 *     agregar() declara quién puede entrar. Nada está escondido.
 *
 * Cómo leer cada línea:
 *   $router->agregar( VERBO, URL, [Controlador::class, 'método'], permiso )
 *
 * Permisos disponibles (definidos en el Router):
 *   (sin 4.º argumento) -> público: cualquiera puede acceder
 *   'sesion'            -> requiere sesión iniciada
 *   'admin'             -> requiere sesión + rol de administrador
 *
 * Segmentos variables:
 *   {id} en la URL acepta solo números; el Router lo valida y lo
 *   pasa como primer argumento al método del controlador.
 *
 * Estructura MVC clásica por capas:
 *   Los controladores están en app/Controladores/:
 *     app/Controladores/AuthController.php        -> App\Controladores\AuthController
 *     app/Controladores/ProductoController.php    -> App\Controladores\ProductoController
 *     app/Controladores/CategoriaController.php   -> App\Controladores\CategoriaController
 *     app/Controladores/UsuarioController.php     -> App\Controladores\UsuarioController
 *     app/Controladores/ExportarController.php    -> App\Controladores\ExportarController
 */

// Importar los controladores de cada módulo con sus namespaces correctos
use App\Controladores\AuthController;
use App\Controladores\ProductoController;
use App\Controladores\CategoriaController;
use App\Controladores\UsuarioController;
use App\Controladores\ExportarController;

/**
 * Función anónima que recibe el Router ya instanciado y le registra
 * todas las rutas. Se retorna para que index.php la invoque con:
 *   $registrarRutas = require 'routes/web.php';
 *   $registrarRutas($router);
 */
return function (App\Core\Router $router): void {

    // ----------------------------------------------------------------
    // AUTENTICACIÓN — rutas públicas (sin sesión requerida)
    // ----------------------------------------------------------------

    // Formulario y procesamiento de inicio de sesión
    $router->agregar('GET',  '/login',     [AuthController::class, 'login']);
    $router->agregar('POST', '/login',     [AuthController::class, 'autenticar']);

    // Formulario y procesamiento de registro de nuevo usuario
    $router->agregar('GET',  '/registro',  [AuthController::class, 'registro']);
    $router->agregar('POST', '/registro',  [AuthController::class, 'registrar']);

    // Formulario y procesamiento de recuperación de contraseña por pregunta secreta
    $router->agregar('GET',  '/recuperar', [AuthController::class, 'recuperar']);
    $router->agregar('POST', '/recuperar', [AuthController::class, 'procesarRecuperar']);

    // Cerrar sesión (GET para que un simple enlace baste)
    $router->agregar('GET',  '/logout',    [AuthController::class, 'logout']);

    // ----------------------------------------------------------------
    // PRODUCTOS — requieren sesión iniciada
    // ----------------------------------------------------------------

    // Dashboard principal: lista + filtros + gráficas
    $router->agregar('GET',  '/',                        [ProductoController::class, 'index'],             'sesion');
    $router->agregar('GET',  '/productos',               [ProductoController::class, 'index'],             'sesion');

    // Formulario de creación
    $router->agregar('GET',  '/productos/crear',         [ProductoController::class, 'crear'],             'sesion');

    // Guardar nuevo producto (recibe el POST del formulario de crear)
    $router->agregar('POST', '/productos',               [ProductoController::class, 'guardar'],           'sesion');

    // Formulario de edición — {id} = ID numérico del producto
    $router->agregar('GET',  '/productos/{id}/editar',   [ProductoController::class, 'editar'],            'sesion');

    // Guardar cambios del producto editado
    $router->agregar('POST', '/productos/{id}',          [ProductoController::class, 'actualizar'],        'sesion');

    // Pantalla de confirmación antes de eliminar
    $router->agregar('GET',  '/productos/{id}/eliminar', [ProductoController::class, 'confirmarEliminar'], 'sesion');

    // Ejecutar la eliminación (POST para que sea acción intencionada)
    $router->agregar('POST', '/productos/{id}/eliminar', [ProductoController::class, 'eliminar'],          'sesion');

    // ----------------------------------------------------------------
    // CATEGORÍAS — requieren sesión iniciada
    // ----------------------------------------------------------------

    // Lista y formulario de nueva categoría en la misma vista
    $router->agregar('GET',  '/categorias',               [CategoriaController::class, 'index'],    'sesion');

    // Crear nueva categoría
    $router->agregar('POST', '/categorias',               [CategoriaController::class, 'guardar'],  'sesion');

    // Eliminar categoría — solo si no tiene productos asignados
    $router->agregar('POST', '/categorias/{id}/eliminar', [CategoriaController::class, 'eliminar'], 'sesion');

    // ----------------------------------------------------------------
    // USUARIOS — SOLO administrador
    // ----------------------------------------------------------------
    // El permiso 'admin' se declara aquí, en la tabla de rutas,
    // no dentro del controlador. Así el control de acceso es visible
    // en un solo lugar y no puede omitirse por error.

    // Lista de todos los usuarios del sistema
    $router->agregar('GET',  '/usuarios',               [UsuarioController::class, 'index'],            'admin');

    // Formulario de creación de nuevo usuario
    $router->agregar('GET',  '/usuarios/crear',         [UsuarioController::class, 'crear'],            'admin');

    // Guardar nuevo usuario
    $router->agregar('POST', '/usuarios',               [UsuarioController::class, 'guardar'],          'admin');

    // Formulario de edición de datos del usuario
    $router->agregar('GET',  '/usuarios/{id}/editar',   [UsuarioController::class, 'editar'],           'admin');

    // Guardar cambios de perfil del usuario
    $router->agregar('POST', '/usuarios/{id}',          [UsuarioController::class, 'actualizar'],       'admin');

    // Resetear contraseña del usuario (formulario separado por seguridad)
    $router->agregar('POST', '/usuarios/{id}/password', [UsuarioController::class, 'resetearPassword'], 'admin');

    // Eliminar usuario — con bloqueos: no puede borrarse a sí mismo
    // ni eliminar al último administrador del sistema
    $router->agregar('POST', '/usuarios/{id}/eliminar', [UsuarioController::class, 'eliminar'],         'admin');

    // ----------------------------------------------------------------
    // EXPORTACIÓN — PDF y Excel (requieren sesión; usuarios solo admin)
    // ----------------------------------------------------------------
    // Estas rutas no modifican datos: solo leen y generan un archivo.
    // Se separan en su propio controlador (app/Controladores/ExportarController)
    // con servicios auxiliares en app/Servicios/ (PdfExporter, ExcelExporter).

    // --- Productos ---
    $router->agregar('GET', '/exportar/productos/pdf',   [ExportarController::class, 'productosPdf'],   'sesion');
    $router->agregar('GET', '/exportar/productos/excel', [ExportarController::class, 'productosExcel'], 'sesion');

    // --- Categorías ---
    $router->agregar('GET', '/exportar/categorias/pdf',   [ExportarController::class, 'categoriasPdf'],   'sesion');
    $router->agregar('GET', '/exportar/categorias/excel', [ExportarController::class, 'categoriasExcel'], 'sesion');

    // --- Usuarios (solo admin) ---
    $router->agregar('GET', '/exportar/usuarios/pdf',   [ExportarController::class, 'usuariosPdf'],   'admin');
    $router->agregar('GET', '/exportar/usuarios/excel', [ExportarController::class, 'usuariosExcel'], 'admin');
};
