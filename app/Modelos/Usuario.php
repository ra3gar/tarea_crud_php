<?php
// =====================================================================
//  app/Modules/Auth/Usuario.php — Lo que la base sabe sobre los usuarios.
//
//  Ubicacion en la arquitectura por modulos:
//    app/Modules/Auth/
//      Usuario.php  <- este archivo (compartido con Modules/Usuarios/)
//
//  Que hace:
//    Reune TODAS las consultas SQL relacionadas con la tabla `usuarios`.
//    Ninguna consulta SQL vive en el controlador: toda la logica de base
//    de datos esta aqui.
//
//  SEGURIDAD:
//    - Nunca se guarda ni se compara una contrasena en texto plano.
//      Los hashes (password_hash / password_verify) viven en el controlador.
//    - Todas las consultas usan prepared statements (parametros con nombre)
//      para prevenir inyeccion SQL, incluso cuando el valor viene de la URL.
// =====================================================================

namespace App\Modelos;

/**
 * Modelo de usuarios.
 *
 * Metodos estaticos agrupados en dos bloques:
 *   - Autenticacion y recuperacion: lo que necesita AuthController.
 *   - Gestion (CRUD): lo que necesita UsuarioController (solo admin).
 *
 * Usa la funcion db() de app/Helpers/funciones.php para obtener la conexion PDO.
 */
class Usuario
{
    // =========================================================================
    //  AUTENTICACION Y RECUPERACION
    // =========================================================================

    /**
     * Busca un usuario por su nombre de login.
     *
     * Devuelve todas las columnas necesarias para:
     *   - Verificar la contrasena (password_hash).
     *   - Guardar datos en la sesion (id, usuario, nombre, rol).
     *
     * @param string $usuario El nombre de login que el usuario escribio.
     * @return array|null     Fila del usuario, o null si no existe.
     */
    public static function porUsuario(string $usuario): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, usuario, nombre, apellido, rol, password_hash
             FROM usuarios WHERE usuario = :u'
        );
        $stmt->execute([':u' => $usuario]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Dice si ya existe un usuario con ese nombre de login.
     *
     * Se usa al registrar y al crear desde el panel, para evitar duplicados.
     * Devuelve bool en vez de la fila entera porque la presencia/ausencia
     * es todo lo que necesita saber el controlador.
     *
     * @param string $usuario Nombre de login a buscar.
     * @return bool           true si ya existe, false si no.
     */
    public static function existe(string $usuario): bool
    {
        $stmt = db()->prepare('SELECT 1 FROM usuarios WHERE usuario = :u');
        $stmt->execute([':u' => $usuario]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Devuelve la pregunta de seguridad y el hash de la respuesta para un usuario.
     *
     * Solo devuelve lo estrictamente necesario para la recuperacion: no devuelve
     * la contrasena ni el rol para no exponer datos de mas.
     *
     * @param string $usuario Nombre de login del usuario.
     * @return array|null     Fila con (pregunta, respuesta_hash), o null si no existe.
     */
    public static function datosRecuperacion(string $usuario): ?array
    {
        $stmt = db()->prepare('SELECT pregunta, respuesta_hash FROM usuarios WHERE usuario = :u');
        $stmt->execute([':u' => $usuario]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Actualiza la contrasena de un usuario buscando por su nombre de login.
     *
     * Se usa en la recuperacion de contrasena, donde el sistema conoce el
     * nombre de login pero no el id.
     *
     * @param string $usuario      Nombre de login del usuario.
     * @param string $passwordHash Hash de la nueva contrasena (ya hasheado por el controlador).
     * @return void
     */
    public static function actualizarPassword(string $usuario, string $passwordHash): void
    {
        db()->prepare('UPDATE usuarios SET password_hash = :ph WHERE usuario = :u')
            ->execute([':ph' => $passwordHash, ':u' => $usuario]);
    }

    // =========================================================================
    //  GESTION DE USUARIOS (CRUD, solo usado por UsuarioController — admin)
    // =========================================================================

    /**
     * Lista todos los usuarios, sin incluir datos sensibles como la contrasena.
     *
     * El orden por id hace que los usuarios mas antiguos aparezcan primero,
     * lo cual es util para ver quien es el administrador original del sistema.
     *
     * @return array<int,array> Filas (id, usuario, nombre, apellido, rol, fecha_creacion).
     */
    public static function todos(): array
    {
        return db()->query(
            'SELECT id, usuario, nombre, apellido, rol, fecha_creacion
             FROM usuarios ORDER BY id'
        )->fetchAll();
    }

    /**
     * Busca un usuario por su id numerico.
     *
     * Se usa al editar o al resetear la contrasena desde el panel de admin.
     * No devuelve el password_hash para no exponerlo en las vistas de edicion.
     *
     * @param int $id El id del usuario a buscar.
     * @return array|null Fila del usuario, o null si no existe.
     */
    public static function porId(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, usuario, nombre, apellido, rol, fecha_creacion
             FROM usuarios WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $u = $stmt->fetch();
        return $u ?: null;
    }

    /**
     * Crea un usuario nuevo en la base de datos.
     *
     * Recibe los datos como un arreglo de marcadores con nombre (:campo => valor)
     * para que el controlador pueda pasarlos directamente al prepared statement.
     *
     * @param array<string,mixed> $d Marcadores con nombre:
     *        :usuario, :nombre, :apellido, :rol,
     *        :password_hash, :pregunta, :respuesta_hash
     * @return void
     */
    public static function crear(array $d): void
    {
        db()->prepare(
            'INSERT INTO usuarios (usuario, nombre, apellido, rol, password_hash, pregunta, respuesta_hash)
             VALUES (:usuario, :nombre, :apellido, :rol, :password_hash, :pregunta, :respuesta_hash)'
        )->execute($d);
    }

    /**
     * Actualiza el perfil de un usuario (nombre, apellido y rol).
     *
     * No toca la contrasena: eso se hace por separado con actualizarPasswordPorId.
     * El rol se valida en el controlador antes de llegar aqui ('admin' o 'usuario').
     *
     * @param int    $id       Id del usuario a modificar.
     * @param string $nombre   Nuevo nombre.
     * @param string $apellido Nuevo apellido.
     * @param string $rol      Nuevo rol ('admin' o 'usuario').
     * @return void
     */
    public static function actualizar(int $id, string $nombre, string $apellido, string $rol): void
    {
        db()->prepare(
            'UPDATE usuarios SET nombre = :n, apellido = :a, rol = :r WHERE id = :id'
        )->execute([':n' => $nombre, ':a' => $apellido, ':r' => $rol, ':id' => $id]);
    }

    /**
     * Resetea la contrasena de un usuario buscando por id (lo usa el admin).
     *
     * El admin no necesita saber la contrasena actual para resetearla: eso es
     * una funcion de administracion, no de recuperacion personal.
     *
     * @param int    $id           Id del usuario.
     * @param string $passwordHash Hash de la nueva contrasena.
     * @return void
     */
    public static function actualizarPasswordPorId(int $id, string $passwordHash): void
    {
        db()->prepare('UPDATE usuarios SET password_hash = :ph WHERE id = :id')
            ->execute([':ph' => $passwordHash, ':id' => $id]);
    }

    /**
     * Borra un usuario por su id.
     *
     * Devuelve la cantidad de filas borradas para que el controlador sepa si
     * el usuario existia o ya habia sido eliminado antes.
     *
     * @param int $id Id del usuario a eliminar.
     * @return int    Filas borradas: 0 si el usuario ya no existia, 1 si se elimino.
     */
    public static function eliminar(int $id): int
    {
        $stmt = db()->prepare('DELETE FROM usuarios WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    /**
     * Cuenta cuantos usuarios tienen el rol 'admin'.
     *
     * Sirve para el candado que evita quedar sin ningun administrador:
     * si solo queda uno, no se puede degradar ni eliminar.
     *
     * @return int Cantidad de usuarios con rol 'admin'.
     */
    public static function contarAdmins(): int
    {
        return (int) db()->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'")->fetchColumn();
    }

    /**
     * Cuenta todos los usuarios del sistema (sin filtrar por rol).
     *
     * Se usa al registrar el primer usuario para decidir si queda como admin:
     * si la tabla esta vacia (resultado = 0), el primero es admin.
     *
     * @return int Total de usuarios en la tabla.
     */
    public static function contarTodos(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    }
}
