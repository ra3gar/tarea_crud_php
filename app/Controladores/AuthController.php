<?php
// =====================================================================
//  app/Modules/Auth/AuthController.php — Entrar, registrarse, recuperar, salir.
//
//  Ubicacion en la arquitectura por modulos:
//    app/Modules/Auth/
//      AuthController.php  <- este archivo
//      Usuario.php         <- modelo del usuario
//      Views/
//        login.php         <- formulario de inicio de sesion
//        registro.php      <- formulario de alta de cuenta
//        recuperar.php     <- recuperacion de contrasena en dos pasos
//
//  Que hace este controlador:
//    - Muestra y procesa el formulario de login
//    - Muestra y procesa el formulario de registro
//    - Gestiona la recuperacion de contrasena (en dos pasos: usuario -> pregunta -> nueva pass)
//    - Cierra la sesion limpiamente
//
//  SEGURIDAD:
//    - La contrasena y la respuesta de seguridad se guardan HASHEADAS en la BD.
//      Nunca se guarda texto plano. Se usa password_hash() al crear y
//      password_verify() al verificar.
//    - Al iniciar sesion correctamente se regenera el id de sesion
//      (session_regenerate_id) para evitar ataques de "fijacion de sesion":
//      un atacante que supiera el id anterior no podria seguir usandolo.
//    - El token CSRF se valida en public/index.php para todos los POST, antes
//      de que llegue a cualquier controlador.
// =====================================================================

namespace App\Controladores;

use App\Modelos\Usuario;

/**
 * Controlador de autenticacion.
 *
 * Responde a las rutas de login, registro, recuperacion y logout.
 * Los metodos que procesan formularios (autenticar, registrar,
 * procesarRecuperar) llegan por POST y ya traen el token CSRF validado.
 */
class AuthController
{
    /**
     * Lista fija de preguntas de seguridad que el sistema ofrece al registrarse.
     *
     * Son preguntas cuya respuesta el usuario recuerda facilmente pero que un
     * atacante no puede adivinar buscando en redes sociales. La lista es fija
     * para no complicar el formulario con un campo de texto libre.
     *
     * @var string[]
     */
    private const PREGUNTAS = [
        'Cual es el nombre de tu primera mascota?',
        'En que ciudad naciste?',
        'Cual es el nombre de tu primera escuela?',
        'Cual es el segundo nombre de tu mama?',
    ];

    // ---- Vistas de inicio de sesion ----

    /**
     * GET /login — muestra el formulario de inicio de sesion.
     *
     * Si el usuario ya tiene una sesion activa no tiene sentido que vea el
     * login, asi que lo manda directo a la lista de productos.
     *
     * @return void
     */
    public function login(): void
    {
        // Si ya esta logueado, no necesita ver el login
        if (isset($_SESSION['usuario_id'])) {
            redirigir('productos');
        }

        // Muestra el formulario vacio, sin errores ni texto previo
        vista('Auth/login', ['errores' => [], 'usuario' => '']);
    }

    /**
     * POST /login — verifica las credenciales y abre la sesion.
     *
     * Pasos:
     *   1. Leer usuario y contrasena del POST.
     *   2. Buscar el usuario en la base de datos.
     *   3. Verificar la contrasena con password_verify() (nunca compara en texto plano).
     *   4. Si coinciden: regenerar el id de sesion y guardar datos del usuario.
     *   5. Si no coinciden: volver al formulario con un mensaje de error generico
     *      (no decir "usuario no existe" ni "contrasena incorrecta" por separado,
     *       porque eso ayudaria a un atacante a adivinar usuarios validos).
     *
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
            // Busca el usuario en la BD (el modelo devuelve null si no existe)
            $u = Usuario::porUsuario($usuario);

            // password_verify compara el texto plano con el hash almacenado
            if ($u && password_verify($pass, $u['password_hash'])) {
                // Exito: renovar el id de sesion antes de guardar datos sensibles
                session_regenerate_id(true);

                // Guardar solo lo necesario en la sesion (minimo privilegio)
                $_SESSION['usuario_id'] = $u['id'];
                $_SESSION['usuario']    = $u['usuario'];
                $_SESSION['nombre']     = $u['nombre'];  // para el saludo en el encabezado
                $_SESSION['rol']        = $u['rol'];     // para las guardias de admin

                redirigir('productos');
            }

            // Mensaje generico: no revela si el usuario existe o no
            $errores[] = 'Usuario o contrasena incorrectos.';
        }

        // Vuelve al formulario con el error y el nombre de usuario escrito (no la pass)
        vista('Auth/login', ['errores' => $errores, 'usuario' => $usuario]);
    }

    // ---- Vistas de registro ----

    /**
     * GET /registro — muestra el formulario de creacion de cuenta.
     *
     * @return void
     */
    public function registro(): void
    {
        vista('Auth/registro', [
            'errores'   => [],
            'usuario'   => '',
            'nombre'    => '',
            'apellido'  => '',
            'pregunta'  => '',
            'preguntas' => self::PREGUNTAS,
        ]);
    }

    /**
     * POST /registro — valida los datos y crea la cuenta nueva.
     *
     * Regla especial del primer usuario: si la tabla de usuarios esta vacia,
     * el primero que se registra queda como administrador. Los siguientes
     * siempre son usuarios normales.
     *
     * Tanto la contrasena como la respuesta de seguridad se guardan como hash
     * (PASSWORD_DEFAULT usa bcrypt): nunca se almacena texto plano.
     *
     * @return void
     */
    public function registrar(): void
    {
        // Leer todos los campos del formulario
        $nombre    = trim($_POST['nombre'] ?? '');
        $apellido  = trim($_POST['apellido'] ?? '');
        $usuario   = trim($_POST['usuario'] ?? '');
        $pass      = $_POST['password'] ?? '';
        $pass2     = $_POST['password2'] ?? '';
        $pregunta  = $_POST['pregunta'] ?? '';
        $respuesta = trim($_POST['respuesta'] ?? '');

        // Validar campo por campo (orden de presentacion al usuario)
        $errores = [];
        if ($nombre === '')                                     $errores[] = 'El nombre es obligatorio.';
        if ($apellido === '')                                   $errores[] = 'El apellido es obligatorio.';
        if ($usuario === '')                                    $errores[] = 'El usuario es obligatorio.';
        if (strlen($pass) < 6)                                 $errores[] = 'La contrasena debe tener al menos 6 caracteres.';
        if ($pass !== $pass2)                                  $errores[] = 'Las contrasenas no coinciden.';
        if (!in_array($pregunta, self::PREGUNTAS, true))       $errores[] = 'Elegi una pregunta de seguridad.';
        if ($respuesta === '')                                  $errores[] = 'La respuesta de seguridad es obligatoria.';
        if (!$errores && Usuario::existe($usuario))            $errores[] = 'Ese usuario ya esta registrado. Elegi otro.';

        // Si hay errores, volver al formulario conservando lo que el usuario escribio
        if ($errores) {
            vista('Auth/registro', [
                'errores'   => $errores,
                'usuario'   => $usuario,
                'nombre'    => $nombre,
                'apellido'  => $apellido,
                'pregunta'  => $pregunta,
                'preguntas' => self::PREGUNTAS,
            ]);
            return;
        }

        // El primer usuario del sistema queda como admin; los siguientes como usuario normal
        $rol = Usuario::contarTodos() === 0 ? 'admin' : 'usuario';

        // Guardar en la BD con la contrasena y la respuesta HASHEADAS
        // mb_strtolower en la respuesta: no importa si el usuario escribe "BUENOS AIRES" o "buenos aires"
        Usuario::crear([
            ':usuario'        => $usuario,
            ':nombre'         => $nombre,
            ':apellido'       => $apellido,
            ':rol'            => $rol,
            ':password_hash'  => password_hash($pass, PASSWORD_DEFAULT),
            ':pregunta'       => $pregunta,
            ':respuesta_hash' => password_hash(mb_strtolower($respuesta), PASSWORD_DEFAULT),
        ]);

        // Redirigir al login con un mensaje de exito
        $_SESSION['mensaje'] = ['texto' => 'Cuenta creada. Ya podes iniciar sesion.', 'tipo' => 'ok'];
        redirigir('login');
    }

    // ---- Recuperacion de contrasena ----

    /**
     * GET /recuperar — muestra el primer paso de la recuperacion (ingresar usuario).
     *
     * @return void
     */
    public function recuperar(): void
    {
        vista('Auth/recuperar', [
            'errores'  => [],
            'usuario'  => '',
            'pregunta' => '',
            'paso2'    => false,  // false = mostrar solo el campo de usuario
        ]);
    }

    /**
     * POST /recuperar — recuperacion en dos pasos dentro del mismo formulario.
     *
     * Paso 1: el usuario ingresa su nombre de login.
     *         El sistema busca la pregunta de seguridad y la muestra.
     *
     * Paso 2: el usuario responde la pregunta y escribe una nueva contrasena.
     *         El sistema verifica la respuesta y, si es correcta, actualiza la pass.
     *
     * El campo oculto "paso" en el HTML distingue en cual de los dos pasos estamos.
     *
     * @return void
     */
    public function procesarRecuperar(): void
    {
        $usuario  = trim($_POST['usuario'] ?? '');
        $paso     = $_POST['paso'] ?? '1';
        $errores  = [];
        $pregunta = '';
        $paso2    = false;

        if ($paso === '1') {
            // ----- Paso 1: buscar el usuario y mostrar su pregunta -----
            if ($usuario === '') {
                $errores[] = 'Ingresa tu usuario.';
            } else {
                $row = Usuario::datosRecuperacion($usuario);
                if ($row) {
                    $pregunta = $row['pregunta'];
                    $paso2    = true;  // mostrar el campo de respuesta y la nueva pass
                } else {
                    $errores[] = 'No encontramos ese usuario.';
                }
            }
        } else {
            // ----- Paso 2: verificar respuesta y actualizar la contrasena -----
            $respuesta = trim($_POST['respuesta'] ?? '');
            $nueva     = $_POST['password'] ?? '';
            $nueva2    = $_POST['password2'] ?? '';

            $row = Usuario::datosRecuperacion($usuario);
            if (!$row) {
                $errores[] = 'Usuario invalido.';
            } else {
                $pregunta = $row['pregunta'];
                $paso2    = true;

                if (strlen($nueva) < 6) $errores[] = 'La nueva contrasena debe tener al menos 6 caracteres.';
                if ($nueva !== $nueva2)  $errores[] = 'Las contrasenas no coinciden.';
                // Verificar la respuesta con su hash almacenado (misma normalizacion de mayusculas)
                if (!password_verify(mb_strtolower($respuesta), $row['respuesta_hash'])) {
                    $errores[] = 'La respuesta de seguridad no es correcta.';
                }

                if (!$errores) {
                    // Todo correcto: guardar la nueva contrasena hasheada
                    Usuario::actualizarPassword($usuario, password_hash($nueva, PASSWORD_DEFAULT));
                    $_SESSION['mensaje'] = ['texto' => 'Contrasena actualizada. Inicia sesion con la nueva.', 'tipo' => 'ok'];
                    redirigir('login');
                }
            }
        }

        // Volver al formulario con el estado actual y los errores
        vista('Auth/recuperar', [
            'errores'  => $errores,
            'usuario'  => $usuario,
            'pregunta' => $pregunta,
            'paso2'    => $paso2,
        ]);
    }

    // ---- Cierre de sesion ----

    /**
     * GET /logout — destruye la sesion y redirige al login.
     *
     * Se limpia el arreglo $_SESSION antes de destruirla para que las cookies
     * de sesion queden invalidas de inmediato y no puedan reutilizarse.
     *
     * @return void
     */
    public function logout(): void
    {
        // Vaciar el arreglo de sesion y luego destruir el archivo de sesion
        $_SESSION = [];
        session_destroy();
        redirigir('login');
    }
}
