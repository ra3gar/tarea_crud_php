<?php
namespace App\Models;

use App\Core\Database;
use PDO;

// =====================================================================
//  app/Models/Producto.php — TODO lo que sabe la base sobre productos.
//  Aca viven las consultas (regla 1: siempre con prepared statements).
//  El controlador no escribe SQL: le pide cosas a este modelo por su
//  nombre y recibe datos ya listos.
// =====================================================================

/**
 * Modelo de productos. Reune todas las consultas de la tabla productos:
 * listar, filtrar, metricas del tablero y el ABM (alta, baja, modificacion).
 */
class Producto
{
    /**
     * Arma el WHERE del filtro UNA sola vez, reutilizable por varias consultas.
     *
     * Lo que decide que condiciones entran es PHP; lo que el usuario escribio
     * NUNCA toca el texto del SQL: viaja aparte, en $params (regla 1).
     *
     * @param string $buscar    Texto a buscar dentro del nombre (vacio = sin filtro).
     * @param string $categoria Categoria exacta a filtrar (vacio = todas).
     * @return array{0:string,1:array<string,string>} [fragmento WHERE, parametros].
     */
    private static function filtro(string $buscar, string $categoria): array
    {
        $cond = [];
        $params = [];
        if ($buscar !== '') {
            $cond[] = 'nombre LIKE :buscar';
            // Escapamos los comodines de LIKE (%, _) para que se busquen literales.
            $literal = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $buscar);
            $params[':buscar'] = '%' . $literal . '%';
        }
        if ($categoria !== '') {
            $cond[] = 'categoria = :categoria';
            $params[':categoria'] = $categoria;
        }
        $where = $cond ? (' WHERE ' . implode(' AND ', $cond)) : '';
        return [$where, $params];
    }

    /**
     * Lista los productos que coinciden con el filtro (o todos si no hay).
     *
     * @param string $buscar    Texto a buscar dentro del nombre.
     * @param string $categoria Categoria a filtrar (vacio = todas).
     * @return array<int,array> Filas de productos, listas para la vista.
     */
    public static function todos(string $buscar = '', string $categoria = ''): array
    {
        [$where, $params] = self::filtro($buscar, $categoria);
        $stmt = Database::pdo()->prepare(
            'SELECT id, nombre, descripcion, precio, stock, categoria, fecha_creacion
             FROM productos' . $where . ' ORDER BY id'
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Busca un producto por su id.
     *
     * @param int $id Id del producto.
     * @return array|null Fila del producto, o null si no existe.
     */
    public static function porId(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM productos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $p = $stmt->fetch();
        return $p ?: null;
    }

    /**
     * Calcula los numeros del tablero (tarjetas de arriba), respetando el filtro.
     *
     * @param string $buscar    Texto a buscar dentro del nombre.
     * @param string $categoria Categoria a filtrar (vacio = todas).
     * @return array{productos:int,unidades:int,valor:float,bajos:int} Metricas.
     */
    public static function metricas(string $buscar = '', string $categoria = ''): array
    {
        [$where, $params] = self::filtro($buscar, $categoria);
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*)                                                 AS productos,
                    COALESCE(SUM(stock), 0)                                  AS unidades,
                    COALESCE(SUM(precio * stock), 0)                         AS valor,
                    COALESCE(SUM(CASE WHEN stock < 10 THEN 1 ELSE 0 END), 0) AS bajos
             FROM productos' . $where
        );
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Valor del inventario (precio x stock) agrupado por categoria.
     * Alimenta el grafico de BARRAS del tablero.
     *
     * @param string $buscar    Texto a buscar dentro del nombre.
     * @param string $categoria Categoria a filtrar (vacio = todas).
     * @return array<int,array{categoria:string,valor:float}> Valor por categoria.
     */
    public static function valorPorCategoria(string $buscar = '', string $categoria = ''): array
    {
        [$where, $params] = self::filtro($buscar, $categoria);
        $stmt = Database::pdo()->prepare(
            "SELECT COALESCE(NULLIF(categoria, ''), 'Sin categoria') AS categoria,
                    SUM(precio * stock)                              AS valor
             FROM productos" . $where . "
             GROUP BY COALESCE(NULLIF(categoria, ''), 'Sin categoria')
             ORDER BY valor DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Unidades de stock agrupadas por categoria (no dinero, piezas).
     * Alimenta el grafico de PASTEL del tablero: que proporcion de las
     * piezas totales esta en cada categoria. Mismo filtro y mismo agrupado
     * que valorPorCategoria.
     *
     * @param string $buscar    Texto a buscar dentro del nombre.
     * @param string $categoria Categoria a filtrar (vacio = todas).
     * @return array<int,array{categoria:string,unidades:int}> Unidades por categoria.
     */
    public static function stockPorCategoria(string $buscar = '', string $categoria = ''): array
    {
        [$where, $params] = self::filtro($buscar, $categoria);
        $stmt = Database::pdo()->prepare(
            "SELECT COALESCE(NULLIF(categoria, ''), 'Sin categoria') AS categoria,
                    SUM(stock)                                       AS unidades
             FROM productos" . $where . "
             GROUP BY COALESCE(NULLIF(categoria, ''), 'Sin categoria')
             ORDER BY unidades DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta todos los productos (sin filtro). Sirve para el "X de Y productos".
     *
     * @return int Total de productos en la tabla.
     */
    public static function contarTodos(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM productos')->fetchColumn();
    }

    /**
     * Inserta un producto nuevo.
     *
     * @param array<string,mixed> $d Marcadores con nombre (:nombre, :descripcion, :precio, :stock, :categoria).
     * @return void
     */
    public static function crear(array $d): void
    {
        Database::pdo()->prepare(
            'INSERT INTO productos (nombre, descripcion, precio, stock, categoria)
             VALUES (:nombre, :descripcion, :precio, :stock, :categoria)'
        )->execute($d);
    }

    /**
     * Actualiza un producto existente.
     *
     * @param int                 $id Id del producto a modificar.
     * @param array<string,mixed> $d  Marcadores con nombre (los mismos campos que crear).
     * @return void
     */
    public static function actualizar(int $id, array $d): void
    {
        $d[':id'] = $id;
        Database::pdo()->prepare(
            'UPDATE productos SET nombre=:nombre, descripcion=:descripcion,
                    precio=:precio, stock=:stock, categoria=:categoria WHERE id=:id'
        )->execute($d);
    }

    /**
     * Borra un producto por su id.
     *
     * @param int $id Id del producto.
     * @return int    Filas borradas: 0 significa que el producto ya no existia.
     */
    public static function eliminar(int $id): int
    {
        $stmt = Database::pdo()->prepare('DELETE FROM productos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    /**
     * Valida los datos de un producto (las mismas tres reglas de siempre).
     *
     * @param array<string,mixed> $d Datos del formulario (nombre, precio, stock, ...).
     * @return array<int,string>     Lista de errores (vacia si esta todo bien).
     */
    public static function validar(array $d): array
    {
        $errores = [];
        if (trim($d['nombre']) === '')                       $errores[] = 'El nombre es obligatorio.';
        if (!is_numeric($d['precio']) || $d['precio'] < 0)   $errores[] = 'El precio debe ser un numero valido (>= 0).';
        if (!is_numeric($d['stock'])  || $d['stock'] < 0)    $errores[] = 'El stock debe ser un numero valido (>= 0).';
        return $errores;
    }
}
