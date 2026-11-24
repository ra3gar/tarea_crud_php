<?php
/**
 * Controlador: CategoriaController
 *
 * Gestiona la sección de categorías del inventario.
 * Las categorías son la lista maestra de la que eligen los productos;
 * tenerlas en una tabla propia evita errores de escritura y facilita reportes.
 *
 * Rutas que maneja:
 *   GET  /categorias              → index()    - lista + formulario de alta
 *   POST /categorias              → guardar()  - inserta una categoría nueva
 *   POST /categorias/{id}/eliminar → eliminar() - borra si no está en uso
 */

namespace App\Controladores;

use App\Modelos\Categoria;

class CategoriaController
{
    // =========================================================
    // LECTURA
    // =========================================================

    /**
     * GET /categorias
     *
     * Muestra la lista de categorías existentes junto con el formulario
     * para agregar una nueva. Se obtienen id+nombre para poder construir
     * el botón de eliminación con el ID correcto.
     *
     * @return void
     */
    public function index(): void
    {
        vista('Categorias/index', [
            'categorias' => Categoria::todas(),
        ]);
    }

    // =========================================================
    // ESCRITURA
    // =========================================================

    /**
     * POST /categorias
     *
     * Valida el nombre recibido del formulario y, si es válido y no existe,
     * lo inserta. En todos los casos guarda un mensaje flash en sesión y
     * redirige de vuelta a /categorias (patrón PRG: Post-Redirect-Get).
     *
     * Validaciones:
     *   1. Nombre no vacío después de trim()
     *   2. Nombre no duplicado (Categoria::existe())
     *
     * @return void
     */
    public function guardar(): void
    {
        // trim() elimina espacios al inicio y al final del nombre
        $nombre = trim($_POST['nombre'] ?? '');

        if ($nombre === '') {
            // No se envió un nombre o solo había espacios
            $_SESSION['mensaje'] = [
                'texto' => 'Escribe un nombre de categoría.',
                'tipo'  => 'error',
            ];
        } elseif (Categoria::existe($nombre)) {
            // Ya hay una categoría con ese nombre exacto
            $_SESSION['mensaje'] = [
                'texto' => 'Esa categoría ya existe.',
                'tipo'  => 'error',
            ];
        } else {
            // Nombre válido y único: se inserta
            Categoria::crear($nombre);
            $_SESSION['mensaje'] = [
                'texto' => 'Categoría agregada.',
                'tipo'  => 'ok',
            ];
        }

        // PRG: redirigir para que un F5 no reenvíe el formulario
        redirigir('categorias');
    }

    /**
     * POST /categorias/{id}/eliminar
     *
     * Elimina la categoría si ningún producto la está usando.
     * Si hay productos con esa categoría se bloquea la eliminación
     * y se informa al usuario con un mensaje flash.
     *
     * @param string $id ID de la categoría (llega como string del router).
     * @return void
     */
    public function eliminar(string $id): void
    {
        // Verificar si algún producto la referencia antes de eliminar
        if (Categoria::enUso((int) $id) > 0) {
            $_SESSION['mensaje'] = [
                'texto' => 'No se puede borrar: hay productos en esa categoría.',
                'tipo'  => 'error',
            ];
        } else {
            Categoria::eliminar((int) $id);
            $_SESSION['mensaje'] = [
                'texto' => 'Categoría eliminada.',
                'tipo'  => 'ok',
            ];
        }

        redirigir('categorias');
    }
}
