<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use App\Middleware\Admin;

// =====================================================================
//  app/Controllers/UsuarioController.php — Gestion de usuarios.
//  SOLO admin: el constructor corre el guardia Admin antes de cualquier
//  accion, asi no hace falta repetir el chequeo en cada metodo.
// =====================================================================
/**
 * Controlador de gestion de usuarios. SOLO admin: el constructor corre el
 * guardia Admin antes de cualquier accion. Cubre listar, crear, editar, rol,
 * resetear contrasena y eliminar (con candados de seguridad).
 */
class UsuarioController extends Controller
{
    /** @var string[] Las mismas preguntas de seguridad que usa el registro. */
    private const PREGUNTAS = [
        'Cual es el nombre de tu primera mascota?',
        'En que ciudad naciste?',
        'Cual es el nombre de tu primera escuela?',
        'Cual es el segundo nombre de tu mama?',
    ];

    /**
     * Corre el guardia de rol: si no sos admin, no entras a ninguna accion.
     */
    public function __construct()
    {
        Admin::proteger();   // si no sos admin, no entras a ninguna accion de aca
    }

    /**
     * GET /usuarios — listado de todos los usuarios.
     * @return void
     */
    public function index(): void
    {
        $this->vista('usuarios/index', [
            'usuarios' => Usuario::todos(),
            'yoId'     => (int) ($_SESSION['usuario_id'] ?? 0),  // para no dejar autoeliminarse
        ]);
    }

    /**
     * GET /usuarios/crear — muestra el formulario de alta vacio.
     * @return void
     */
    public function crear(): void
    {
        $this->vista('usuarios/crear', [
            'datos'     => ['usuario' => '', 'nombre' => '', 'apellido' => '', 'rol' => 'usuario', 'pregunta' => ''],
            'preguntas' => self::PREGUNTAS,
            'errores'   => [],
        ]);
    }

    /**
     * POST /usuarios — valida y crea un usuario nuevo (o vuelve con errores).
     * @return void
     */
    public function guardar(): void
    {
        $d         = $this->datosDelFormulario();
        $pass      = $_POST['password'] ?? '';
        $pass2     = $_POST['password2'] ?? '';
        $respuesta = trim($_POST['respuesta'] ?? '');

        $errores = $this->validarPerfil($d);
        if (strlen($pass) < 6)  $errores[] = 'La contrasena debe tener al menos 6 caracteres.';
        if ($pass !== $pass2)   $errores[] = 'Las contrasenas no coinciden.';
        if (!in_array($d['pregunta'], self::PREGUNTAS, true)) $errores[] = 'Elegi una pregunta de seguridad.';
        if ($respuesta === '')  $errores[] = 'La respuesta de seguridad es obligatoria.';
        if (!$errores && Usuario::existe($d['usuario'])) $errores[] = 'Ese usuario ya existe. Elegi otro.';

        if ($errores) {
            $this->vista('usuarios/crear', ['datos' => $d, 'preguntas' => self::PREGUNTAS, 'errores' => $errores]);
            return;
        }
        Usuario::crear([
            ':usuario'        => $d['usuario'],
            ':nombre'         => $d['nombre'],
            ':apellido'       => $d['apellido'],
            ':rol'            => $d['rol'],
            ':password_hash'  => password_hash($pass, PASSWORD_DEFAULT),
            ':pregunta'       => $d['pregunta'],
            ':respuesta_hash' => password_hash(mb_strtolower($respuesta), PASSWORD_DEFAULT),
        ]);
        $_SESSION['mensaje'] = ['texto' => 'Usuario creado.', 'tipo' => 'ok'];
        $this->redirigir('usuarios');
    }

    /**
     * GET /usuarios/{id}/editar — formulario con el usuario ya cargado.
     * @param string $id Id del usuario.
     * @return void
     */
    public function editar(string $id): void
    {
        $u = Usuario::porId((int) $id);
        if (!$u) {
            $_SESSION['mensaje'] = ['texto' => 'Ese usuario ya no existe.', 'tipo' => 'error'];
            $this->redirigir('usuarios');
        }
        $this->vista('usuarios/editar', ['u' => $u, 'errores' => []]);
    }

    /**
     * POST /usuarios/{id} — guarda nombre, apellido y rol. Candado: no deja
     * quitar al ultimo administrador del sistema.
     * @param string $id Id del usuario a modificar.
     * @return void
     */
    public function actualizar(string $id): void
    {
        $id = (int) $id;
        $u  = Usuario::porId($id);
        if (!$u) {
            $_SESSION['mensaje'] = ['texto' => 'Ese usuario ya no existe.', 'tipo' => 'error'];
            $this->redirigir('usuarios');
        }
        $nombre   = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $rol      = ($_POST['rol'] ?? '') === 'admin' ? 'admin' : 'usuario';

        $errores = [];
        if ($nombre === '')   $errores[] = 'El nombre es obligatorio.';
        if ($apellido === '') $errores[] = 'El apellido es obligatorio.';
        // Candado: no dejar al sistema sin ningun administrador.
        if ($u['rol'] === 'admin' && $rol === 'usuario' && Usuario::contarAdmins() <= 1) {
            $errores[] = 'No podes quitar el ultimo administrador del sistema.';
        }
        if ($errores) {
            $u2 = array_merge($u, ['nombre' => $nombre, 'apellido' => $apellido, 'rol' => $rol]);
            $this->vista('usuarios/editar', ['u' => $u2, 'errores' => $errores]);
            return;
        }
        Usuario::actualizar($id, $nombre, $apellido, $rol);
        $_SESSION['mensaje'] = ['texto' => 'Usuario actualizado.', 'tipo' => 'ok'];
        $this->redirigir('usuarios');
    }

    /**
     * POST /usuarios/{id}/password — el admin resetea la contrasena del usuario.
     * @param string $id Id del usuario.
     * @return void
     */
    public function resetearPassword(string $id): void
    {
        $id = (int) $id;
        $u  = Usuario::porId($id);
        if (!$u) {
            $_SESSION['mensaje'] = ['texto' => 'Ese usuario ya no existe.', 'tipo' => 'error'];
            $this->redirigir('usuarios');
        }
        $pass  = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';
        if (strlen($pass) < 6 || $pass !== $pass2) {
            $_SESSION['mensaje'] = ['texto' => 'La contrasena debe tener 6+ caracteres y coincidir.', 'tipo' => 'error'];
            $this->redirigir('usuarios/' . $id . '/editar');
        }
        Usuario::actualizarPasswordPorId($id, password_hash($pass, PASSWORD_DEFAULT));
        $_SESSION['mensaje'] = ['texto' => 'Contrasena actualizada.', 'tipo' => 'ok'];
        $this->redirigir('usuarios');
    }

    /**
     * POST /usuarios/{id}/eliminar — borra, con dos candados: no podes
     * eliminarte a vos mismo ni dejar al sistema sin ningun admin.
     * @param string $id Id del usuario.
     * @return void
     */
    public function eliminar(string $id): void
    {
        $id = (int) $id;
        // Candado 1: no podes eliminar tu propia cuenta.
        if ($id === (int) ($_SESSION['usuario_id'] ?? 0)) {
            $_SESSION['mensaje'] = ['texto' => 'No podes eliminar tu propia cuenta.', 'tipo' => 'error'];
            $this->redirigir('usuarios');
        }
        // Candado 2: no podes dejar al sistema sin ningun admin.
        $u = Usuario::porId($id);
        if ($u && $u['rol'] === 'admin' && Usuario::contarAdmins() <= 1) {
            $_SESSION['mensaje'] = ['texto' => 'No podes eliminar el ultimo administrador.', 'tipo' => 'error'];
            $this->redirigir('usuarios');
        }
        $filas = Usuario::eliminar($id);
        $_SESSION['mensaje'] = $filas
            ? ['texto' => 'Usuario eliminado.', 'tipo' => 'ok']
            : ['texto' => 'Ese usuario ya no existe.', 'tipo' => 'error'];
        $this->redirigir('usuarios');
    }

    // --- ayudas privadas ---

    /**
     * Junta y limpia los campos del formulario de usuario.
     * @return array<string,string> Datos del usuario (usuario, nombre, apellido, rol, pregunta).
     */
    private function datosDelFormulario(): array
    {
        return [
            'usuario'  => trim($_POST['usuario'] ?? ''),
            'nombre'   => trim($_POST['nombre'] ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'rol'      => ($_POST['rol'] ?? '') === 'admin' ? 'admin' : 'usuario',
            'pregunta' => $_POST['pregunta'] ?? '',
        ];
    }

    /**
     * Valida los campos de perfil obligatorios (usuario, nombre, apellido).
     * @param array<string,string> $d Datos del formulario.
     * @return array<int,string>      Lista de errores (vacia si esta todo bien).
     */
    private function validarPerfil(array $d): array
    {
        $errores = [];
        if ($d['usuario'] === '')  $errores[] = 'El usuario (login) es obligatorio.';
        if ($d['nombre'] === '')   $errores[] = 'El nombre es obligatorio.';
        if ($d['apellido'] === '') $errores[] = 'El apellido es obligatorio.';
        return $errores;
    }
}
