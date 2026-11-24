<?php
/**
 * Modelo: Categoria
 *
 * Encapsula todas las consultas SQL relacionadas con la tabla `categorias`.
 * Las categorías se gestionan en su propia sección del sistema; los productos
 * las referencian por nombre para mantener la integridad sin usar FK externas.
 *
 * Patrón: métodos estáticos + función auxiliar db() que devuelve PDO.
 * Todas las consultas usan prepared statements para prevenir inyección SQL.
 */

namespace App\Modules\Categorias;

use PDO;

class Categoria
{
    // =========================================================
    // CONSULTAS
    // =========================================================

    /**
     * Devuelve todas las categorías (id y nombre), ordenadas alfabéticamente.
     *
     * Se usa en la vista index.php de categorías para construir la tabla.
     *
     * @return array<int,array{id:int,nombre:string}>
     */
    public static function todas(): array
    {
        return db()
            ->query('SELECT id, nombre FROM categorias ORDER BY nombre')
            ->fetchAll();
    }

    /**
     * Devuelve solo los nombres de categoría, como array simple de strings.
     *
     * Se usa para poblar los <select> en los formularios de productos;
     * así el usuario elige de una lista y no escribe nombres a mano.
     *
     * @return array<int,string>
     */
    public static function nombres(): array
    {
        return db()
            ->query('SELECT nombre FROM categorias ORDER BY nombre')
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    // =========================================================
    // VALIDACIÓN / CONTROL
    // =========================================================

    /**
     * Verifica si ya existe una categoría con ese nombre exacto.
     *
     * Se llama antes de insertar para evitar duplicados.
     * La comparación depende del collation de la tabla (normalmente ci = case-insensitive).
     *
     * @param string $nombre Nombre a verificar.
     * @return bool          true si ya existe, false si es nuevo.
     */
    public static function existe(string $nombre): bool
    {
        $stmt = db()->prepare('SELECT 1 FROM categorias WHERE nombre = :n');
        $stmt->execute([':n' => $nombre]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Cuenta cuántos productos usan esta categoría.
     *
     * Se llama antes de eliminar: si devuelve > 0 se bloquea la eliminación
     * para no dejar productos sin categoría de forma inesperada.
     *
     * @param int $id ID de la categoría a verificar.
     * @return int    Cantidad de productos que la referencian.
     */
    public static function enUso(int $id): int
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM productos p
             JOIN categorias c ON c.nombre = p.categoria
             WHERE c.id = :id'
        );
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    // =========================================================
    // ESCRITURA (CRUD)
    // =========================================================

    /**
     * Inserta una nueva categoría en la base de datos.
     *
     * @param string $nombre Nombre de la categoría a crear.
     * @return void
     */
    public static function crear(string $nombre): void
    {
        db()
            ->prepare('INSERT INTO categorias (nombre) VALUES (:n)')
            ->execute([':n' => $nombre]);
    }

    /**
     * Elimina una categoría por su ID.
     *
     * Solo debe llamarse después de confirmar que enUso() devuelve 0.
     *
     * @param int $id ID de la categoría a eliminar.
     * @return void
     */
    public static function eliminar(int $id): void
    {
        db()
            ->prepare('DELETE FROM categorias WHERE id = :id')
            ->execute([':id' => $id]);
    }
}
