<?php
// =====================================================================
//  app/Modules/Productos/ProductoController.php — Orquesta el modulo de productos.
//
//  Ubicacion en la arquitectura por modulos:
//    app/Modules/Productos/
//      ProductoController.php  <- este archivo
//      Producto.php            <- modelo
//      Views/                  <- vistas
//
//  Que hace este controlador:
//    Lee la peticion HTTP, le pide datos al modelo Producto (y a Categoria
//    para los desplegables), y elige que vista mostrar. No escribe SQL ni
//    arma HTML: un metodo por accion del CRUD.
//
//  Rutas que responde (configuradas en routes/web.php):
//    GET  /          -> index()   (dashboard + listado)
//    GET  /productos -> index()
//    GET  /productos/crear        -> crear()
//    POST /productos              -> guardar()
//    GET  /productos/{id}/editar  -> editar($id)
//    POST /productos/{id}         -> actualizar($id)
//    GET  /productos/{id}/eliminar -> confirmarEliminar($id)
//    POST /productos/{id}/eliminar -> eliminar($id)
// =====================================================================

namespace App\Modules\Productos;

use App\Modules\Categorias\Categoria;

/**
 * Controlador de productos.
 *
 * Responde a las ocho acciones del CRUD de productos.
 * Las rutas POST ya vienen con el token CSRF validado por index.php.
 */
class ProductoController
{
    /**
     * GET / y GET /productos — tablero con metricas + listado con filtro.
     *
     * El filtro (buscar y categoria) viene de la query string (?buscar=...&categoria=...).
     * Si no hay filtro, se muestran todos los productos.
     * Se pasan tanto los datos filtrados como el total sin filtro para el mensaje
     * "Mostrando X de Y productos".
     *
     * @return void
     */
    public function index(): void
    {
        // Leer el filtro de la query string (GET), limpiando espacios al principio y al final
        $buscar    = trim($_GET['buscar'] ?? '');
        $categoria = trim($_GET['categoria'] ?? '');

        vista('Productos/index', [
            // Listado de productos filtrado
            'productos'          => Producto::todos($buscar, $categoria),
            // Metricas de las cuatro tarjetas del tablero (con el mismo filtro)
            'm'                  => Producto::metricas($buscar, $categoria),
            // Datos para el grafico de barras (valor por categoria)
            'porCategoria'       => Producto::valorPorCategoria($buscar, $categoria),
            // Datos para el grafico de pastel (unidades por categoria)
            'stockPorCategoria'  => Producto::stockPorCategoria($buscar, $categoria),
            // Lista de nombres para el desplegable de filtro por categoria
            'categorias'         => Categoria::nombres(),
            // Total sin filtro: para mostrar "X de Y productos"
            'totalSinFiltro'     => Producto::contarTodos(),
            // Los valores del filtro actual (para pre-llenar el formulario de busqueda)
            'buscar'             => $buscar,
            'categoria'          => $categoria,
            // true si hay algun filtro activo (para mostrar el boton "Limpiar filtro")
            'hayFiltro'          => ($buscar !== '' || $categoria !== ''),
        ]);
    }

    /**
     * GET /productos/crear — muestra el formulario de alta vacio.
     *
     * Pasa datos vacios con la misma estructura que usa el formulario al
     * volver con errores: asi la vista siempre trabaja con las mismas variables.
     *
     * @return void
     */
    public function crear(): void
    {
        vista('Productos/crear', [
            'categorias' => Categoria::nombres(),
            // Datos vacios para pre-llenar (el formulario de alta comienza vacio)
            'datos' => [
                'nombre'      => '',
                'descripcion' => '',
                'precio'      => '',
                'stock'       => '',
                'categoria'   => '',
            ],
            'errores' => [],
        ]);
    }

    /**
     * POST /productos — valida los datos y guarda el producto nuevo.
     *
     * Si hay errores de validacion, vuelve al formulario con los datos escritos
     * por el usuario (para no obligarlo a escribirlos de nuevo).
     *
     * @return void
     */
    public function guardar(): void
    {
        $datos   = $this->datosDelFormulario();
        $errores = Producto::validar($datos);

        if ($errores) {
            // Volver al formulario con los errores y los datos escritos
            vista('Productos/crear', [
                'categorias' => Categoria::nombres(),
                'datos'      => $datos,
                'errores'    => $errores,
            ]);
            return;
        }

        // Sin errores: guardar y volver al listado con confirmacion
        Producto::crear($this->paraGuardar($datos));
        $_SESSION['mensaje'] = ['texto' => 'Producto creado.', 'tipo' => 'ok'];
        redirigir('productos');
    }

    /**
     * GET /productos/{id}/editar — formulario con el producto ya cargado.
     *
     * Si el producto no existe (fue borrado por otro usuario), muestra un
     * mensaje de error y vuelve al listado.
     *
     * @param string $id Id del producto (llega como texto desde la URL).
     * @return void
     */
    public function editar(string $id): void
    {
        $p = Producto::porId((int) $id);
        if (!$p) {
            $_SESSION['mensaje'] = ['texto' => 'Ese producto ya no existe.', 'tipo' => 'error'];
            redirigir('productos');
        }

        vista('Productos/editar', [
            'id'         => (int) $id,
            'categorias' => Categoria::nombres(),
            'datos'      => $p,       // los datos actuales del producto
            'errores'    => [],
        ]);
    }

    /**
     * POST /productos/{id} — valida y guarda los cambios del producto.
     *
     * @param string $id Id del producto a modificar.
     * @return void
     */
    public function actualizar(string $id): void
    {
        $datos   = $this->datosDelFormulario();
        $errores = Producto::validar($datos);

        if ($errores) {
            vista('Productos/editar', [
                'id'         => (int) $id,
                'categorias' => Categoria::nombres(),
                'datos'      => $datos,
                'errores'    => $errores,
            ]);
            return;
        }

        Producto::actualizar((int) $id, $this->paraGuardar($datos));
        $_SESSION['mensaje'] = ['texto' => 'Producto actualizado.', 'tipo' => 'ok'];
        redirigir('productos');
    }

    /**
     * GET /productos/{id}/eliminar — pantalla de confirmacion antes de borrar.
     *
     * No borra nada: muestra los datos del producto y pide confirmacion.
     * El borrado real lo hace eliminar() cuando el usuario confirma.
     *
     * @param string $id Id del producto.
     * @return void
     */
    public function confirmarEliminar(string $id): void
    {
        $p = Producto::porId((int) $id);
        if (!$p) {
            $_SESSION['mensaje'] = ['texto' => 'Ese producto ya no existe.', 'tipo' => 'error'];
            redirigir('productos');
        }

        // Pasa los datos del producto para mostrarlos en la confirmacion
        vista('Productos/eliminar', ['id' => (int) $id, 'datos' => $p]);
    }

    /**
     * POST /productos/{id}/eliminar — elimina el producto definitivamente.
     *
     * rowCount() devuelve 0 si el producto ya habia sido borrado antes: en ese
     * caso se muestra un mensaje de "ya no existe" en vez de "eliminado".
     *
     * @param string $id Id del producto.
     * @return void
     */
    public function eliminar(string $id): void
    {
        $filas = Producto::eliminar((int) $id);
        $_SESSION['mensaje'] = $filas
            ? ['texto' => 'Producto eliminado.', 'tipo' => 'ok']
            : ['texto' => 'Ese producto ya no existe.', 'tipo' => 'error'];
        redirigir('productos');
    }

    // =========================================================================
    //  METODOS PRIVADOS DE APOYO
    // =========================================================================

    /**
     * Lee y limpia los campos del formulario de producto.
     *
     * Centralizar la lectura del POST evita repetir el mismo codigo en
     * guardar() y actualizar(). trim() elimina espacios al principio y al final
     * que el usuario pudo haber dejado sin querer.
     *
     * @return array<string,string> Datos del producto tal como llegaron (antes de validar).
     */
    private function datosDelFormulario(): array
    {
        return [
            'nombre'      => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio'      => trim($_POST['precio'] ?? ''),
            'stock'       => trim($_POST['stock'] ?? ''),
            'categoria'   => trim($_POST['categoria'] ?? ''),
        ];
    }

    /**
     * Convierte los datos del formulario en marcadores con nombre para PDO.
     *
     * El campo categoria se guarda como NULL cuando llega vacio, en vez de
     * como cadena vacia ''. Esto respeta la clave foranea: '' no existe en la
     * tabla de categorias, pero NULL si es un valor valido (producto sin categoria).
     *
     * @param array<string,string> $d Datos ya validados del formulario.
     * @return array<string,mixed>    Marcadores (:nombre, :descripcion, ...).
     */
    private function paraGuardar(array $d): array
    {
        return [
            ':nombre'      => $d['nombre'],
            ':descripcion' => $d['descripcion'],
            ':precio'      => $d['precio'],
            ':stock'       => $d['stock'],
            // Categoria vacia = NULL en la BD (no '' que romperia la FK)
            ':categoria'   => $d['categoria'] === '' ? null : $d['categoria'],
        ];
    }
}
