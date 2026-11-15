<?php
namespace App\Models;

use App\Core\Database;

// =====================================================================
//  app/Models/Usuario.php — Lo que la base sabe sobre los usuarios.
//  SEGURIDAD: aca nunca se guarda ni se compara una contrasena en texto;
//  siempre son hashes (password_hash / password_verify en el controlador).
//  Todas las consultas usan prepared statements (regla 1).
// =====================================================================

/**
 * Modelo de usuarios. Cubre la autenticacion, la recuperacion por pregunta
 * de seguridad y el CRUD de gestion (que solo usa el admin desde el panel).
 */
class Usuario
{
    // ---- Autenticacion y recuperacion ----

    /**
     * Trae un usuario por su login, con lo necesario para iniciar sesion
     * (verificar la contrasena) y para saludar/guardar el rol en la sesion.
     *
     * @param string $usuario Login del usuario.
     * @return array|null     Fila (id, usuario, nombre, apellido, rol, password_hash) o null.
     */
    public static function porUsuario(string $usuario): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, usuario, nombre, apellido, rol, password_hash
             FROM usuarios WHERE usuario = :u'
        );
        $stmt->execute([':u' => $usuario]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Dice si ya existe un usuario con ese login (para no repetir).
     *
     * @param string $usuario Login a buscar.
     * @return bool           true si ya existe, false si no.
     */
    public static function existe(string $usuario): bool
    {
        $stmt = Database::pdo()->prepare('SELECT 1 FROM usuarios WHERE usuario = :u');
        $stmt->execute([':u' => $usuario]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Devuelve la pregunta de seguridad y el hash de su respuesta.
     * Lo usa la recuperacion de contrasena.
     *
     * @param string $usuario Login del usuario.
     * @return array|null     Fila (pregunta, respuesta_hash) o null si no existe.
     */
    public static function datosRecuperacion(string $usuario): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT pregunta, respuesta_hash FROM usuarios WHERE usuario = :u');
        $stmt->execute([':u' => $usuario]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Cambia la contrasena buscando por login (lo usa la recuperacion).
     *
     * @param string $usuario      Login del usuario.
     * @param string $passwordHash Hash de la nueva contrasena.
     * @return void
     */
    public static function actualizarPassword(string $usuario, string $passwordHash): void
    {
        Database::pdo()->prepare('UPDATE usuarios SET password_hash = :ph WHERE usuario = :u')
            ->execute([':ph' => $passwordHash, ':u' => $usuario]);
    }

    // ---- Gestion de usuarios (CRUD, solo admin desde el controlador) ----

    /**
     * Lista todos los usuarios (sin los hashes), para la tabla del panel.
     *
     * @return array<int,array> Filas (id, usuario, nombre, apellido, rol, fecha_creacion).
     */
    public static function todos(): array
    {
        return Database::pdo()->query(
            'SELECT id, usuario, nombre, apellido, rol, fecha_creacion
             FROM usuarios ORDER BY id'
        )->fetchAll();
    }

    /**
     * Busca un usuario por su id (sin el hash de la contrasena).
     *
     * @param int $id Id del usuario.
     * @return array|null Fila del usuario o null si no existe.
     */
    public static function porId(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, usuario, nombre, apellido, rol, fecha_creacion
             FROM usuarios WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $u = $stmt->fetch();
        return $u ?: null;
    }

    /**
     * Crea un usuario nuevo.
     *
     * @param array<string,mixed> $d Marcadores con nombre
     *        (:usuario, :nombre, :apellido, :rol, :password_hash, :pregunta, :respuesta_hash).
     * @return void
     */
    public static function crear(array $d): void
    {
        Database::pdo()->prepare(
            'INSERT INTO usuarios (usuario, nombre, apellido, rol, password_hash, pregunta, respuesta_hash)
             VALUES (:usuario, :nombre, :apellido, :rol, :password_hash, :pregunta, :respuesta_hash)'
        )->execute($d);
    }

    /**
     * Edita los datos de perfil y el rol (no toca la contrasena: eso va aparte).
     *
     * @param int    $id       Id del usuario a modificar.
     * @param string $nombre   Nombre nuevo.
     * @param string $apellido Apellido nuevo.
     * @param string $rol      Rol nuevo ('admin' o 'usuario').
     * @return void
     */
    public static function actualizar(int $id, string $nombre, string $apellido, string $rol): void
    {
        Database::pdo()->prepare(
            'UPDATE usuarios SET nombre = :n, apellido = :a, rol = :r WHERE id = :id'
        )->execute([':n' => $nombre, ':a' => $apellido, ':r' => $rol, ':id' => $id]);
    }

    /**
     * Resetea la contrasena buscando por id (lo usa el admin desde el panel).
     *
     * @param int    $id           Id del usuario.
     * @param string $passwordHash Hash de la nueva contrasena.
     * @return void
     */
    public static function actualizarPasswordPorId(int $id, string $passwordHash): void
    {
        Database::pdo()->prepare('UPDATE usuarios SET password_hash = :ph WHERE id = :id')
            ->execute([':ph' => $passwordHash, ':id' => $id]);
    }

    /**
     * Borra un usuario por su id.
     *
     * @param int $id Id del usuario.
     * @return int    Filas borradas: 0 significa que el usuario ya no existia.
     */
    public static function eliminar(int $id): int
    {
        $stmt = Database::pdo()->prepare('DELETE FROM usuarios WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    /**
     * Cuantos admins hay. Sirve para el candado "no dejar al sistema sin admin".
     *
     * @return int Cantidad de usuarios con rol 'admin'.
     */
    public static function contarAdmins(): int
    {
        return (int) Database::pdo()->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'")->fetchColumn();
    }

    /**
     * Cuantos usuarios hay en total. Sirve para hacer admin al primero.
     *
     * @return int Total de usuarios en la tabla.
     */
    public static function contarTodos(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    }
}
