<?php
namespace App\Core;

use App\Middleware\Auth;

// =====================================================================
//  app/Core/Router.php — El que decide, segun la URL, que controlador corre.
//  routes/web.php le carga una tabla de rutas; despues, por cada peticion,
//  busca la que coincide y llama al metodo del controlador.
// =====================================================================

/**
 * Enrutador: guarda una tabla de rutas y, por cada peticion, elige a quien
 * le toca responder.
 */
class Router
{
    /** @var array<int,array> Tabla de rutas registradas (metodo, patron, accion, privada). */
    private array $rutas = [];

    /**
     * Registra una ruta en la tabla.
     *
     * @param string              $metodo  Verbo HTTP: "GET" o "POST".
     * @param string              $patron  Forma de la URL (ej. "/productos/{id}/editar").
     * @param array{0:string,1:string} $accion [NombreDelControlador::class, 'metodo'] a ejecutar.
     * @param bool                $privada true si la ruta exige sesion iniciada.
     * @return void
     */
    public function agregar(string $metodo, string $patron, array $accion, bool $privada = false): void
    {
        $this->rutas[] = compact('metodo', 'patron', 'accion', 'privada');
    }

    /**
     * Busca la ruta que coincide con la peticion y ejecuta su controlador.
     *
     * Si ninguna coincide, responde 404. Si la ruta es privada, antes de
     * seguir corre el guardia de sesion (Auth).
     *
     * @param string $metodo Verbo HTTP de la peticion actual ("GET"/"POST").
     * @param string $ruta   Ruta pedida (ej. "/productos/5/editar").
     * @return void          Imprime la respuesta del controlador, o un 404.
     */
    public function despachar(string $metodo, string $ruta): void
    {
        foreach ($this->rutas as $r) {
            if ($r['metodo'] !== $metodo) {
                continue;
            }
            // Convierte /productos/{id}/editar en una expresion que ademas
            // captura el id donde dice {id}. \d+ = solo numeros, asi
            // /productos/crear nunca se confunde con /productos/{id}.
            $regex = '#^' . preg_replace('#\{[a-z]+\}#', '(\d+)', $r['patron']) . '$#';
            if (preg_match($regex, $ruta, $coincidencias)) {
                // Si la ruta es privada, el guardia corta antes de seguir.
                if ($r['privada']) {
                    Auth::proteger();
                }
                array_shift($coincidencias);            // saca la coincidencia completa
                [$clase, $metodoCtrl] = $r['accion'];
                (new $clase())->$metodoCtrl(...$coincidencias);
                return;
            }
        }
        http_response_code(404);
        echo 'Pagina no encontrada (404).';
    }
}
