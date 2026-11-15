<?php
namespace App\Middleware;

// =====================================================================
//  app/Middleware/Admin.php — Guardia de ROL (complementa a Auth).
//  Auth ya verifico que haya sesion; este revisa que el rol sea 'admin'.
//  Si no lo es, avisa y manda a Productos. Asi /usuarios queda cerrado
//  para los usuarios normales (regla 2 de seguridad, a nivel de rol).
// =====================================================================

/**
 * Guardia de rol. Solo deja pasar a los usuarios con rol 'admin'.
 * Lo corre el constructor de UsuarioController antes de cada accion.
 */
class Admin
{
    /**
     * Si el usuario de la sesion no es admin, deja un aviso y lo manda a
     * Productos, cortando la ejecucion. Si es admin, no hace nada.
     *
     * @return void
     */
    public static function proteger(): void
    {
        if (($_SESSION['rol'] ?? '') !== 'admin') {
            $_SESSION['mensaje'] = ['texto' => 'Solo un administrador puede gestionar usuarios.', 'tipo' => 'error'];
            header('Location: ' . url('productos'));
            exit;
        }
    }
}
