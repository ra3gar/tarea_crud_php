<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Categoria;

// =====================================================================
//  app/Controllers/CategoriaController.php — La ventana de categorias.
//  Lista las categorias y permite agregar y borrar. Los productos eligen
//  de esta lista, asi se deja de escribir categorias a mano.
// =====================================================================
/**
 * Controlador de categorias: lista, agrega y borra. Los productos eligen de
 * esta lista, asi se deja de escribir categorias a mano.
 */
class CategoriaController extends Controller
{
    /**
     * GET /categorias — lista las categorias + formulario para agregar.
     * @return void
     */
    public function index(): void
    {
        $this->vista('categorias/index', ['categorias' => Categoria::todas()]);
    }

    /**
     * POST /categorias — agrega una categoria nueva (si no existe ya).
     * @return void
     */
    public function guardar(): void
    {
        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') {
            $_SESSION['mensaje'] = ['texto' => 'Escribi un nombre de categoria.', 'tipo' => 'error'];
        } elseif (Categoria::existe($nombre)) {
            $_SESSION['mensaje'] = ['texto' => 'Esa categoria ya existe.', 'tipo' => 'error'];
        } else {
            Categoria::crear($nombre);
            $_SESSION['mensaje'] = ['texto' => 'Categoria agregada.', 'tipo' => 'ok'];
        }
        $this->redirigir('categorias');
    }

    /**
     * POST /categorias/{id}/eliminar — borra la categoria si no esta en uso.
     * @param string $id Id de la categoria.
     * @return void
     */
    public function eliminar(string $id): void
    {
        if (Categoria::enUso((int) $id) > 0) {
            $_SESSION['mensaje'] = ['texto' => 'No se puede borrar: hay productos en esa categoria.', 'tipo' => 'error'];
        } else {
            Categoria::eliminar((int) $id);
            $_SESSION['mensaje'] = ['texto' => 'Categoria eliminada.', 'tipo' => 'ok'];
        }
        $this->redirigir('categorias');
    }
}
