<?php
namespace App\Models;

use App\Core\Database;
use PDO;

// =====================================================================
//  app/Models/Categoria.php — Lo que la base sabe sobre las categorias.
//  Las categorias viven en su propia tabla: se eligen de una lista en vez
//  de escribirlas, y se administran desde su propia ventana.
// =====================================================================

/**
 * Modelo de categorias. Todas las consultas usan prepared statements
 * (regla 1 de seguridad). El controlador le pide datos; aca vive el SQL.
 */
class Categoria
{
    /**
     * Trae todas las categorias (id y nombre), ordenadas por nombre.
     *
     * @return array<int,array{id:int,nombre:string}> Filas de categorias.
     */
    public static function todas(): array
    {
        return Database::pdo()
            ->query('SELECT id, nombre FROM categorias ORDER BY nombre')
            ->fetchAll();
    }

    /**
     * Solo los nombres, para poblar los desplegables de los formularios.
     *
     * @return array<int,string> Lista de nombres de categoria.
     */
    public static function nombres(): array
    {
        return Database::pdo()
            ->query('SELECT nombre FROM categorias ORDER BY nombre')
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Dice si ya existe una categoria con ese nombre (para no repetir).
     *
     * @param string $nombre Nombre a buscar.
     * @return bool          true si ya existe, false si no.
     */
    public static function existe(string $nombre): bool
    {
        $stmt = Database::pdo()->prepare('SELECT 1 FROM categorias WHERE nombre = :n');
        $stmt->execute([':n' => $nombre]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Inserta una categoria nueva.
     *
     * @param string $nombre Nombre de la categoria a crear.
     * @return void
     */
    public static function crear(string $nombre): void
    {
        Database::pdo()
            ->prepare('INSERT INTO categorias (nombre) VALUES (:n)')
            ->execute([':n' => $nombre]);
    }

    /**
     * Cuenta cuantos productos usan esta categoria (para no borrar una en uso).
     *
     * @param int $id Id de la categoria.
     * @return int    Cantidad de productos que la usan (0 = se puede borrar).
     */
    public static function enUso(int $id): int
    {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM productos p
             JOIN categorias c ON c.nombre = p.categoria
             WHERE c.id = :id'
        );
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Borra una categoria por su id.
     *
     * @param int $id Id de la categoria a eliminar.
     * @return void
     */
    public static function eliminar(int $id): void
    {
        Database::pdo()->prepare('DELETE FROM categorias WHERE id = :id')->execute([':id' => $id]);
    }
}
