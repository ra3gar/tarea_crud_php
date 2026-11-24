<?php
/**
 * Controlador: UsuarioController
 *
 * Gestiona la administración de cuentas de usuario del sistema.
 *
 * IMPORTANTE SOBRE PERMISOS:
 *   Este controlador solo debe ser alcanzado por administradores.
 *   El permiso NO se comprueba aquí sino en routes/web.php, donde las rutas
 *   de /usuarios llevan el permiso 'admin'. El Router llama a proteger_admin()
 *   antes de invocar cualquier método. Así el permiso queda en la tabla de rutas,
 *   visible y auditable, y no escondido en el código del controlador.
 *
 * Rutas que maneja:
 *   GET  /usuarios               → index()           - listado de usuarios
 *   GET  /usuarios/crear         → crear()           - formulario de alta
 *   POST /usuarios               → guardar()         - procesa el alta
 *   GET  /usuarios/{id}/editar   → editar()          - formulario de edición
 *   POST /usuarios/{id}          → actualizar()      - guarda nombre/apellido/rol
 *   POST /usuarios/{id}/password → resetearPassword() - el admin cambia la contraseña
 *   POST /usuarios/{id}/eliminar → eliminar()        - borra el usuario
 */

namespace App\Controladores;

// El modelo Usuario vive en el módulo Auth porque también lo usa AuthController
use App\Modelos\Usuario;

class UsuarioController
{
    /**
     * Preguntas de seguridad disponibles al crear un usuario.
     * Deben coincidir exactamente con las del AuthController para que la
     * recuperación de contraseña funcione correctamente.
     *
     * @var string[]
     */
    private const PREGUNTAS = [
        'Cual es el nombre de tu primera mascota?',
        'En que ciudad naciste?',
        'Cual es el nombre de tu primera escuela?',
        'Cual es el segundo nombre de tu mama?',
    ];

    // =========================================================
    // LECTURA
    // =========================================================

    /**
     * GET /usuarios
     *
     * Muestra todos los usuarios registrados en el sistema.
     * Se pasa $yoId para que la vista pueda deshabilitar el botón
     * de eliminar del usuario actualmente conectado (evitar autoelimación).
     *
     * @return void
     */
    public function index(): void
    {
        vista('Usuarios/index', [
            'usuarios' => Usuario::todos(),
            // ID del usuario de la sesión actual (para ocultar el botón "Eliminar" propio)
            'yoId'     => (int) ($_SESSION['usuario_id'] ?? 0),
        ]);
    }

    // =========================================================
    // CREACIÓN
    // =========================================================

    /**
     * GET /usuarios/crear
     *
     * Muestra el formulario vacío para registrar un usuario nuevo.
     * Se inicializan $datos con cadenas vacías para que la vista no
     * necesite comprobar isset() en cada campo.
     *
     * @return void
     */
    public function crear(): void
    {
        vista('Usuarios/crear', [
            'datos' => [
                'usuario'  => '',
                'nombre'   => '',
                'apellido' => '',
                'rol'      => 'usuario', // rol por defecto al crear
                'pregunta' => '',
            ],
            'preguntas' => self::PREGUNTAS,
            'errores'   => [],
        ]);
    }

    /**
     * POST /usuarios
     *
     * Valida el formulario de alta y crea el usuario si todo es correcto.
     * Si hay errores, vuelve a mostrar el formulario con los datos ingresados
     * y la lista de errores (sin perder lo que el usuario escribió).
     *
     * Validaciones:
     *   - usuario, nombre, apellido: obligatorios
     *   - contraseña: mínimo 6 caracteres y debe coincidir con la confirmación
     *   - pregunta de seguridad: debe ser una de la lista definida
     *   - respuesta de seguridad: obligatoria
     *   - nombre de usuario: no debe existir ya en la base de datos
     *
     * @return void
     */
    public function guardar(): void
    {
        // Recoger y sanear los campos del formulario
        $d         = $this->datosDelFormulario();
        $pass      = $_POST['password']  ?? '';
        $pass2     = $_POST['password2'] ?? '';
        $respuesta = trim($_POST['respuesta'] ?? '');

        // Validar campos básicos de perfil (usuario, nombre, apellido)
        $errores = $this->validarPerfil($d);

        // Validar contraseña: longitud mínima y confirmación
        if (strlen($pass) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        }
        if ($pass !== $pass2) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        // Validar que se eligió una pregunta de seguridad de la lista oficial
        if (!in_array($d['pregunta'], self::PREGUNTAS, true)) {
            $errores[] = 'Elige una pregunta de seguridad.';
        }

        // Validar que se proporcionó una respuesta de seguridad
        if ($respuesta === '') {
            $errores[] = 'La respuesta de seguridad es obligatoria.';
        }

        // Verificar que el nombre de usuario no esté ya registrado
        // (solo si no hay errores previos para no consultar la DB innecesariamente)
        if (!$errores && Usuario::existe($d['usuario'])) {
            $errores[] = 'Ese usuario ya existe. Elige otro.';
        }

        // Si hay errores, mostrar el formulario con los datos y los mensajes
        if ($errores) {
            vista('Usuarios/crear', [
                'datos'     => $d,
                'preguntas' => self::PREGUNTAS,
                'errores'   => $errores,
            ]);
            return;
        }

        // Sin errores: insertar el usuario en la base de datos
        // Las contraseñas y respuestas nunca se guardan en texto plano (bcrypt)
        Usuario::crear([
            ':usuario'        => $d['usuario'],
            ':nombre'         => $d['nombre'],
            ':apellido'       => $d['apellido'],
            ':rol'            => $d['rol'],
            ':password_hash'  => password_hash($pass, PASSWORD_DEFAULT),
            ':pregunta'       => $d['pregunta'],
            // La respuesta se guarda en minúsculas para comparación case-insensitive
            ':respuesta_hash' => password_hash(mb_strtolower($respuesta), PASSWORD_DEFAULT),
        ]);

        $_SESSION['mensaje'] = ['texto' => 'Usuario creado.', 'tipo' => 'ok'];
        // PRG: redirigir para que un F5 no reenvíe el formulario
        redirigir('usuarios');
    }

    // =========================================================
    // EDICIÓN
    // =========================================================

    /**
     * GET /usuarios/{id}/editar
     *
     * Carga los datos del usuario y muestra el formulario pre-cargado.
     * Si el ID no existe (por ejemplo, fue eliminado en otra pestaña),
     * redirige con un mensaje de error.
     *
     * @param string $id ID del usuario a editar.
     * @return void
     */
    public function editar(string $id): void
    {
        $u = Usuario::porId((int) $id);

        if (!$u) {
            $_SESSION['mensaje'] = ['texto' => 'Ese usuario ya no existe.', 'tipo' => 'error'];
            redirigir('usuarios');
        }

        vista('Usuarios/editar', [
            'u'      => $u,
            'errores'=> [],
        ]);
    }

    /**
     * POST /usuarios/{id}
     *
     * Guarda los cambios de nombre, apellido y rol de un usuario existente.
     *
     * Candado de seguridad: no se puede quitar el rol 'admin' al último
     * administrador del sistema, porque quedaría sin nadie que pueda gestionar usuarios.
     *
     * @param string $id ID del usuario a actualizar.
     * @return void
     */
    public function actualizar(string $id): void
    {
        $id = (int) $id;

        // Verificar que el usuario sigue existiendo
        $u = Usuario::porId($id);
        if (!$u) {
            $_SESSION['mensaje'] = ['texto' => 'Ese usuario ya no existe.', 'tipo' => 'error'];
            redirigir('usuarios');
        }

        // Recoger los nuevos valores del formulario
        $nombre   = trim($_POST['nombre']   ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        // El rol solo puede ser 'admin' o 'usuario'; cualquier otro valor se trata como 'usuario'
        $rol      = ($_POST['rol'] ?? '') === 'admin' ? 'admin' : 'usuario';

        $errores = [];
        if ($nombre   === '') $errores[] = 'El nombre es obligatorio.';
        if ($apellido === '') $errores[] = 'El apellido es obligatorio.';

        // Candado: no dejar al sistema sin ningún administrador
        if ($u['rol'] === 'admin' && $rol === 'usuario' && Usuario::contarAdmins() <= 1) {
            $errores[] = 'No puedes quitar el último administrador del sistema.';
        }

        if ($errores) {
            // Mezclar datos originales con los nuevos para pre-cargar el formulario
            $u2 = array_merge($u, [
                'nombre'   => $nombre,
                'apellido' => $apellido,
                'rol'      => $rol,
            ]);
            vista('Usuarios/editar', ['u' => $u2, 'errores' => $errores]);
            return;
        }

        Usuario::actualizar($id, $nombre, $apellido, $rol);
        $_SESSION['mensaje'] = ['texto' => 'Usuario actualizado.', 'tipo' => 'ok'];
        redirigir('usuarios');
    }

    /**
     * POST /usuarios/{id}/password
     *
     * Permite a un administrador establecer una nueva contraseña para un usuario.
     * El administrador no ve la contraseña actual; simplemente la sobreescribe.
     * Útil cuando un usuario olvidó su contraseña y no puede recuperarla.
     *
     * @param string $id ID del usuario al que se le resetea la contraseña.
     * @return void
     */
    public function resetearPassword(string $id): void
    {
        $id = (int) $id;

        $u = Usuario::porId($id);
        if (!$u) {
            $_SESSION['mensaje'] = ['texto' => 'Ese usuario ya no existe.', 'tipo' => 'error'];
            redirigir('usuarios');
        }

        $pass  = $_POST['password']  ?? '';
        $pass2 = $_POST['password2'] ?? '';

        // Validar longitud mínima y confirmación
        if (strlen($pass) < 6 || $pass !== $pass2) {
            $_SESSION['mensaje'] = [
                'texto' => 'La contraseña debe tener 6+ caracteres y coincidir.',
                'tipo'  => 'error',
            ];
            redirigir('usuarios/' . $id . '/editar');
            return;
        }

        // Guardar el nuevo hash bcrypt
        Usuario::actualizarPasswordPorId($id, password_hash($pass, PASSWORD_DEFAULT));
        $_SESSION['mensaje'] = ['texto' => 'Contraseña actualizada.', 'tipo' => 'ok'];
        redirigir('usuarios');
    }

    // =========================================================
    // ELIMINACIÓN
    // =========================================================

    /**
     * POST /usuarios/{id}/eliminar
     *
     * Elimina un usuario del sistema con dos candados de seguridad:
     *   1. No puedes eliminar tu propia cuenta (te quedarías sin sesión activa)
     *   2. No puedes eliminar al último administrador (el sistema quedaría sin admin)
     *
     * @param string $id ID del usuario a eliminar.
     * @return void
     */
    public function eliminar(string $id): void
    {
        $id = (int) $id;

        // Candado 1: el usuario conectado no puede eliminar su propia cuenta
        if ($id === (int) ($_SESSION['usuario_id'] ?? 0)) {
            $_SESSION['mensaje'] = [
                'texto' => 'No puedes eliminar tu propia cuenta.',
                'tipo'  => 'error',
            ];
            redirigir('usuarios');
            return;
        }

        // Candado 2: no se puede eliminar al último administrador del sistema
        $u = Usuario::porId($id);
        if ($u && $u['rol'] === 'admin' && Usuario::contarAdmins() <= 1) {
            $_SESSION['mensaje'] = [
                'texto' => 'No puedes eliminar el último administrador.',
                'tipo'  => 'error',
            ];
            redirigir('usuarios');
            return;
        }

        // Sin restricciones: proceder con la eliminación
        $filas = Usuario::eliminar($id);
        $_SESSION['mensaje'] = $filas
            ? ['texto' => 'Usuario eliminado.',        'tipo' => 'ok']
            : ['texto' => 'Ese usuario ya no existe.', 'tipo' => 'error'];

        redirigir('usuarios');
    }

    // =========================================================
    // MÉTODOS PRIVADOS (helpers del controlador)
    // =========================================================

    /**
     * Recoge y sanea los campos comunes del formulario de usuario.
     *
     * Centraliza el trim() y la asignación por defecto para no repetirlo
     * en guardar() y actualizar().
     *
     * @return array<string,string> Datos saneados del formulario.
     */
    private function datosDelFormulario(): array
    {
        return [
            'usuario'  => trim($_POST['usuario']  ?? ''),
            'nombre'   => trim($_POST['nombre']   ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            // Solo dos roles válidos; cualquier otro se trata como 'usuario'
            'rol'      => ($_POST['rol'] ?? '') === 'admin' ? 'admin' : 'usuario',
            'pregunta' => $_POST['pregunta'] ?? '',
        ];
    }

    /**
     * Valida los campos básicos de perfil: usuario, nombre y apellido.
     *
     * @param array<string,string> $d Datos recogidos del formulario.
     * @return array<int,string>      Lista de mensajes de error (vacía = todo correcto).
     */
    private function validarPerfil(array $d): array
    {
        $errores = [];
        if ($d['usuario']  === '') $errores[] = 'El usuario (login) es obligatorio.';
        if ($d['nombre']   === '') $errores[] = 'El nombre es obligatorio.';
        if ($d['apellido'] === '') $errores[] = 'El apellido es obligatorio.';
        return $errores;
    }
}
