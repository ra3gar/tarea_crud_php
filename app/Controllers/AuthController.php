<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;

// =====================================================================
//  app/Controllers/AuthController.php — Entrar, registrarse, recuperar, salir.
//  SEGURIDAD: contrasena y respuesta se guardan HASHEADAS (regla: nunca en
//  texto). Al entrar bien se regenera el id de sesion (evita fijacion).
// =====================================================================
/**
 * Controlador de autenticacion: entrar, registrarse, recuperar y salir.
 */
class AuthController extends Controller
{
    /** @var string[] Preguntas de seguridad posibles (lista fija). */
    private const PREGUNTAS = [
        'Cual es el nombre de tu primera mascota?',
        'En que ciudad naciste?',
        'Cual es el nombre de tu primera escuela?',
        'Cual es el segundo nombre de tu mama?',
    ];

    /**
     * GET /login — muestra el formulario (o manda a Productos si ya hay sesion).
     * @return void
     */
    public function login(): void
    {
        if (isset($_SESSION['usuario_id'])) $this->redirigir('productos');
        $this->vista('auth/login', ['errores' => [], 'usuario' => '']);
    }

    /**
     * POST /login — verifica usuario y contrasena. Si coinciden, abre sesion
     * (regenerando el id para evitar fijacion) y guarda nombre y rol. Si no,
     * vuelve al formulario con el error.
     * @return void
     */
    public function autenticar(): void
    {
        $usuario = trim($_POST['usuario'] ?? '');
        $pass    = $_POST['password'] ?? '';
        $errores = [];
        if ($usuario === '' || $pass === '') {
            $errores[] = 'Completa usuario y contrasena.';
        } else {
            $u = Usuario::porUsuario($usuario);
            if ($u && password_verify($pass, $u['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $u['id'];
                $_SESSION['usuario']    = $u['usuario'];
                $_SESSION['nombre']     = $u['nombre'];   // para el saludo
                $_SESSION['rol']        = $u['rol'];      // para el guardia de admin
                $this->redirigir('productos');
            }
            $errores[] = 'Usuario o contrasena incorrectos.';
        }
        $this->vista('auth/login', ['errores' => $errores, 'usuario' => $usuario]);
    }

    /**
     * GET /registro — muestra el formulario de alta vacio.
     * @return void
     */
    public function registro(): void
    {
        $this->vista('auth/registro', ['errores' => [], 'usuario' => '', 'nombre' => '', 'apellido' => '', 'pregunta' => '', 'preguntas' => self::PREGUNTAS]);
    }

    /**
     * POST /registro — valida y crea la cuenta. El PRIMER usuario del sistema
     * queda admin; los demas, usuario normal. La contrasena y la respuesta de
     * seguridad se guardan hasheadas.
     * @return void
     */
    public function registrar(): void
    {
        $nombre    = trim($_POST['nombre'] ?? '');
        $apellido  = trim($_POST['apellido'] ?? '');
        $usuario   = trim($_POST['usuario'] ?? '');
        $pass      = $_POST['password'] ?? '';
        $pass2     = $_POST['password2'] ?? '';
        $pregunta  = $_POST['pregunta'] ?? '';
        $respuesta = trim($_POST['respuesta'] ?? '');
        $errores = [];
        if ($nombre === '')                          $errores[] = 'El nombre es obligatorio.';
        if ($apellido === '')                        $errores[] = 'El apellido es obligatorio.';
        if ($usuario === '')                         $errores[] = 'El usuario es obligatorio.';
        if (strlen($pass) < 6)                       $errores[] = 'La contrasena debe tener al menos 6 caracteres.';
        if ($pass !== $pass2)                         $errores[] = 'Las contrasenas no coinciden.';
        if (!in_array($pregunta, self::PREGUNTAS, true)) $errores[] = 'Elegi una pregunta de seguridad.';
        if ($respuesta === '')                       $errores[] = 'La respuesta de seguridad es obligatoria.';
        if (!$errores && Usuario::existe($usuario))  $errores[] = 'Ese usuario ya esta registrado. Elegi otro.';

        if ($errores) {
            $this->vista('auth/registro', [
                'errores' => $errores, 'usuario' => $usuario,
                'nombre' => $nombre, 'apellido' => $apellido,
                'pregunta' => $pregunta, 'preguntas' => self::PREGUNTAS,
            ]);
            return;
        }
        // El PRIMER usuario del sistema queda admin; los siguientes, usuario normal.
        $rol = Usuario::contarTodos() === 0 ? 'admin' : 'usuario';
        Usuario::crear([
            ':usuario'        => $usuario,
            ':nombre'         => $nombre,
            ':apellido'       => $apellido,
            ':rol'            => $rol,
            ':password_hash'  => password_hash($pass, PASSWORD_DEFAULT),
            ':pregunta'       => $pregunta,
            ':respuesta_hash' => password_hash(mb_strtolower($respuesta), PASSWORD_DEFAULT),  // respuesta tambien hasheada
        ]);
        $_SESSION['mensaje'] = ['texto' => 'Cuenta creada. Ya podes iniciar sesion.', 'tipo' => 'ok'];
        $this->redirigir('login');
    }

    /**
     * GET /recuperar — primer paso de la recuperacion (pedir el usuario).
     * @return void
     */
    public function recuperar(): void
    {
        $this->vista('auth/recuperar', ['errores' => [], 'usuario' => '', 'pregunta' => '', 'paso2' => false]);
    }

    /**
     * POST /recuperar — recuperacion en dos pasos: el paso 1 muestra la
     * pregunta del usuario; el paso 2 valida la respuesta y cambia la clave.
     * @return void
     */
    public function procesarRecuperar(): void
    {
        $usuario = trim($_POST['usuario'] ?? '');
        $paso    = $_POST['paso'] ?? '1';
        $errores = [];
        $pregunta = '';
        $paso2 = false;

        if ($paso === '1') {
            if ($usuario === '') {
                $errores[] = 'Ingresa tu usuario.';
            } else {
                $row = Usuario::datosRecuperacion($usuario);
                if ($row) { $pregunta = $row['pregunta']; $paso2 = true; }
                else      { $errores[] = 'No encontramos ese usuario.'; }
            }
        } else { // paso 2
            $respuesta = trim($_POST['respuesta'] ?? '');
            $nueva  = $_POST['password'] ?? '';
            $nueva2 = $_POST['password2'] ?? '';
            $row = Usuario::datosRecuperacion($usuario);
            if (!$row) {
                $errores[] = 'Usuario invalido.';
            } else {
                $pregunta = $row['pregunta']; $paso2 = true;
                if (strlen($nueva) < 6) $errores[] = 'La nueva contrasena debe tener al menos 6 caracteres.';
                if ($nueva !== $nueva2) $errores[] = 'Las contrasenas no coinciden.';
                if (!password_verify(mb_strtolower($respuesta), $row['respuesta_hash'])) $errores[] = 'La respuesta de seguridad no es correcta.';
                if (!$errores) {
                    Usuario::actualizarPassword($usuario, password_hash($nueva, PASSWORD_DEFAULT));
                    $_SESSION['mensaje'] = ['texto' => 'Contrasena actualizada. Inicia sesion con la nueva.', 'tipo' => 'ok'];
                    $this->redirigir('login');
                }
            }
        }
        $this->vista('auth/recuperar', ['errores' => $errores, 'usuario' => $usuario, 'pregunta' => $pregunta, 'paso2' => $paso2]);
    }

    /**
     * GET /logout — vacia y destruye la sesion, y vuelve al login.
     * @return void
     */
    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirigir('login');
    }
}
