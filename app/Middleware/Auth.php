<?php
namespace App\Middleware;

// =====================================================================
//  app/Middleware/Auth.php — El guardia de sesion (regla 2 de seguridad).
//  El Router lo corre ANTES de las rutas marcadas como privadas. Si no hay
//  sesion iniciada, manda al login y corta. Una sola definicion de "privado",
//  en vez de repetir el chequeo en cada pagina.
// =====================================================================

/**
 * Guardia de sesion. Protege las rutas privadas: si no hay usuario logueado,
 * no deja seguir.
 */
class Auth
{
    /**
     * Si no hay sesion iniciada, redirige al login y corta la ejecucion.
     * Si la hay, no hace nada y la peticion sigue su curso.
     *
     * @return void
     */
    public static function proteger(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . url('login'));
            exit;
        }
    }
}
