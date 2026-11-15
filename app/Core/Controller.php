<?php
namespace App\Core;

// =====================================================================
//  app/Core/Controller.php — Clase base de todos los controladores.
//  Trae dos ayudas que todos usan: mostrar una vista y redirigir.
// =====================================================================

/**
 * Controlador base. Los demas controladores la extienden para heredar
 * dos herramientas comunes: pintar una vista y redirigir a otra ruta.
 */
class Controller
{
    /**
     * Carga una vista y le pasa datos.
     *
     * extract() convierte un arreglo como ['productos' => [...]] en una
     * variable $productos disponible dentro de la vista. La vista solo arma
     * HTML: no toca la base de datos.
     *
     * @param string               $nombre Ruta de la vista sin .php (ej. "productos/index").
     * @param array<string,mixed>  $datos  Datos que la vista va a usar, por nombre.
     * @return void                        Imprime el HTML de la vista.
     */
    protected function vista(string $nombre, array $datos = []): void
    {
        extract($datos);
        require __DIR__ . '/../Views/' . $nombre . '.php';
    }

    /**
     * Redirige el navegador a otra ruta de la app y corta la ejecucion.
     *
     * @param string $ruta Ruta interna destino (ej. "productos").
     * @return void        Envia la cabecera Location y termina con exit.
     */
    protected function redirigir(string $ruta): void
    {
        header('Location: ' . url($ruta));
        exit;
    }
}
