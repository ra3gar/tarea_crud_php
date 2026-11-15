<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Producto;
use App\Models\Categoria;

// =====================================================================
//  app/Controllers/ProductoController.php — Orquesta lo de productos.
//  No escribe SQL ni arma HTML: lee la peticion, le pide datos al Modelo,
//  y elige que Vista mostrar. Un metodo por accion.
// =====================================================================
/**
 * Controlador de productos. Lee la peticion, le pide datos al modelo y elige
 * la vista. No escribe SQL ni arma HTML: un metodo por accion.
 */
class ProductoController extends Controller
{
    /**
     * GET / y GET /productos — listado de productos + tablero (con filtro).
     * @return void
     */
    public function index(): void
    {
        $buscar    = trim($_GET['buscar'] ?? '');
        $categoria = trim($_GET['categoria'] ?? '');

        $this->vista('productos/index', [
            'productos'      => Producto::todos($buscar, $categoria),
            'm'              => Producto::metricas($buscar, $categoria),
            'porCategoria'   => Producto::valorPorCategoria($buscar, $categoria),
            'stockPorCategoria' => Producto::stockPorCategoria($buscar, $categoria),
            'categorias'     => Categoria::nombres(),
            'totalSinFiltro' => Producto::contarTodos(),
            'buscar'         => $buscar,
            'categoria'      => $categoria,
            'hayFiltro'      => ($buscar !== '' || $categoria !== ''),
        ]);
    }

    /**
     * GET /productos/crear — muestra el formulario de alta vacio.
     * @return void
     */
    public function crear(): void
    {
        $this->vista('productos/crear', [
            'categorias' => Categoria::nombres(),
            'datos'      => ['nombre' => '', 'descripcion' => '', 'precio' => '', 'stock' => '', 'categoria' => ''],
            'errores'    => [],
        ]);
    }

    /**
     * POST /productos — valida y guarda un producto nuevo (o vuelve con errores).
     * @return void
     */
    public function guardar(): void
    {
        $datos = $this->datosDelFormulario();
        $errores = Producto::validar($datos);
        if ($errores) {
            // Vuelve a mostrar el formulario con los errores y lo ya escrito.
            $this->vista('productos/crear', [
                'categorias' => Categoria::nombres(),
                'datos'      => $datos,
                'errores'    => $errores,
            ]);
            return;
        }
        Producto::crear($this->paraGuardar($datos));
        $_SESSION['mensaje'] = ['texto' => 'Producto creado.', 'tipo' => 'ok'];
        $this->redirigir('productos');
    }

    /**
     * GET /productos/{id}/editar — formulario con el producto ya cargado.
     * @param string $id Id del producto (llega como texto desde la URL).
     * @return void
     */
    public function editar(string $id): void
    {
        $p = Producto::porId((int) $id);
        if (!$p) {
            $_SESSION['mensaje'] = ['texto' => 'Ese producto ya no existe.', 'tipo' => 'error'];
            $this->redirigir('productos');
        }
        $this->vista('productos/editar', [
            'id'         => (int) $id,
            'categorias' => Categoria::nombres(),
            'datos'      => $p,
            'errores'    => [],
        ]);
    }

    /**
     * POST /productos/{id} — valida y guarda los cambios del producto.
     * @param string $id Id del producto a modificar.
     * @return void
     */
    public function actualizar(string $id): void
    {
        $datos = $this->datosDelFormulario();
        $errores = Producto::validar($datos);
        if ($errores) {
            $this->vista('productos/editar', [
                'id'         => (int) $id,
                'categorias' => Categoria::nombres(),
                'datos'      => $datos,
                'errores'    => $errores,
            ]);
            return;
        }
        Producto::actualizar((int) $id, $this->paraGuardar($datos));
        $_SESSION['mensaje'] = ['texto' => 'Producto actualizado.', 'tipo' => 'ok'];
        $this->redirigir('productos');
    }

    /**
     * GET /productos/{id}/eliminar — pide confirmacion antes de borrar.
     * @param string $id Id del producto.
     * @return void
     */
    public function confirmarEliminar(string $id): void
    {
        $p = Producto::porId((int) $id);
        if (!$p) {
            $_SESSION['mensaje'] = ['texto' => 'Ese producto ya no existe.', 'tipo' => 'error'];
            $this->redirigir('productos');
        }
        $this->vista('productos/eliminar', ['id' => (int) $id, 'datos' => $p]);
    }

    /**
     * POST /productos/{id}/eliminar — borra el producto de verdad.
     * @param string $id Id del producto.
     * @return void
     */
    public function eliminar(string $id): void
    {
        $filas = Producto::eliminar((int) $id);
        $_SESSION['mensaje'] = $filas
            ? ['texto' => 'Producto eliminado.', 'tipo' => 'ok']
            : ['texto' => 'Ese producto ya no existe.', 'tipo' => 'error'];
        $this->redirigir('productos');
    }

    // --- ayudas privadas ---

    /**
     * Junta y limpia los campos del formulario de producto.
     * @return array<string,string> Datos del producto tal como llegaron.
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
     * Arma los marcadores con nombre que espera el INSERT/UPDATE del modelo.
     * @param array<string,string> $d Datos ya validados del formulario.
     * @return array<string,string> Marcadores (:nombre, :descripcion, ...).
     */
    private function paraGuardar(array $d): array
    {
        return [
            ':nombre'      => $d['nombre'],
            ':descripcion' => $d['descripcion'],
            ':precio'      => $d['precio'],
            ':stock'       => $d['stock'],
            ':categoria'   => $d['categoria'],
        ];
    }
}
