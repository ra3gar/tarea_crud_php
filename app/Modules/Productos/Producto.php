<?php
// =====================================================================
//  app/Modules/Productos/Producto.php — Todo lo que la base sabe sobre productos.
//
//  Ubicacion en la arquitectura por modulos:
//    app/Modules/Productos/
//      Producto.php           <- este archivo (modelo)
//      ProductoController.php <- controlador
//      Views/                 <- vistas del modulo
//
//  Que hace:
//    Reune TODAS las consultas SQL de la tabla `productos`.
//    El controlador no escribe SQL: le pide cosas a este modelo por nombre
//    y recibe datos ya listos para mostrar en la vista.
//
//  SEGURIDAD:
//    Todas las consultas usan prepared statements (parametros con nombre).
//    Lo que el usuario escribe NUNCA toca el texto del SQL: viaja aparte
//    en el arreglo de parametros que PDO maneja de forma segura.
// =====================================================================

namespace App\Modules\Productos;

/**
 * Modelo de productos.
 *
 * Metodos estaticos agrupados en cuatro bloques:
 *   - Filtro reutilizable (WHERE compartido por varias consultas)
 *   - Consultas de listado y metricas (tablero)
 *   - CRUD (crear, leer por id, actualizar, eliminar)
 *   - Validacion
 */
class Producto
{
    // =========================================================================
    //  FILTRO REUTILIZABLE
    // =========================================================================

    /**
     * Construye el fragmento WHERE y los parametros del filtro de busqueda.
     *
     * Por que existe: varias consultas (todos, metricas, graficos) usan
     * exactamente el mismo filtro. En vez de repetir el codigo, se extrae
     * a un metodo privado que devuelve las dos piezas que PDO necesita:
     *   1. El texto SQL con los placeholders (:buscar, :categoria).
     *   2. El arreglo de parametros para execute().
     *
     * Seguridad: solo la ESTRUCTURA de la condicion viene de PHP. Los VALORES
     * que escribio el usuario van en $params y los maneja PDO; nunca se
     * concatenan al texto del SQL.
     *
     * @param string $buscar    Texto a buscar dentro del nombre ('' = sin filtro).
     * @param string $categoria Nombre exacto de categoria ('' = todas).
     * @return array{0:string,1:array<string,string>} [fragmento WHERE, parametros].
     */
    private static function filtro(string $buscar, string $categoria): array
    {
        $condiciones = [];
        $params      = [];

        if ($buscar !== '') {
            $condiciones[] = 'nombre LIKE :buscar';
            // Escapar los comodines de LIKE para que se busquen como literales:
            // "50%" no debe encontrar "50 unidades", sino el texto "50%".
            $literal           = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $buscar);
            $params[':buscar'] = '%' . $literal . '%';
        }

        if ($categoria !== '') {
            $condiciones[] = 'categoria = :categoria';
            $params[':categoria'] = $categoria;
        }

        // Si no hay condiciones, el WHERE queda como cadena vacia (sin filtro)
        $where = $condiciones ? (' WHERE ' . implode(' AND ', $condiciones)) : '';

        return [$where, $params];
    }

    // =========================================================================
    //  CONSULTAS DE LISTADO Y METRICAS
    // =========================================================================

    /**
     * Devuelve todos los productos que coinciden con el filtro, o todos si no hay.
     *
     * El orden por id es el orden de creacion: los mas nuevos aparecen al final.
     * Esto facilita ver el historial de altas sin necesidad de paginar.
     *
     * @param string $buscar    Texto a buscar dentro del nombre.
     * @param string $categoria Categoria a filtrar ('' = todas).
     * @return array<int,array> Filas de productos listas para la vista.
     */
    public static function todos(string $buscar = '', string $categoria = ''): array
    {
        [$where, $params] = self::filtro($buscar, $categoria);
        $stmt = db()->prepare(
            'SELECT id, nombre, descripcion, precio, stock, categoria, fecha_creacion
             FROM productos' . $where . ' ORDER BY id'
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Busca un producto por su id.
     *
     * Se usa al editar o al confirmar la eliminacion: necesitamos saber si
     * el producto todavia existe (pudo haber sido borrado por otro usuario).
     *
     * @param int $id El id numerico del producto.
     * @return array|null Fila del producto, o null si no existe.
     */
    public static function porId(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM productos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $p = $stmt->fetch();
        return $p ?: null;
    }

    /**
     * Calcula las cuatro metricas de las tarjetas del tablero.
     *
     * Se hace en una sola consulta SQL para evitar cuatro viajes a la base.
     * COALESCE convierte NULL en 0 cuando la tabla esta vacia o el filtro
     * no devuelve filas (evita mostrar "null" en el tablero).
     *
     * @param string $buscar    Texto a buscar dentro del nombre.
     * @param string $categoria Categoria a filtrar ('' = todas).
     * @return array{productos:int,unidades:int,valor:float,bajos:int} Metricas.
     */
    public static function metricas(string $buscar = '', string $categoria = ''): array
    {
        [$where, $params] = self::filtro($buscar, $categoria);
        $stmt = db()->prepare(
            'SELECT
               COUNT(*)                                                 AS productos,
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
     *
     * Alimenta el grafico de BARRAS del tablero. Muestra cuanto dinero
     * hay inmovilizado en cada categoria.
     *
     * COALESCE(NULLIF(categoria, ''), 'Sin categoria') convierte categoria
     * vacia o NULL en la etiqueta "Sin categoria" para que el grafico
     * no quede con una barra sin nombre.
     *
     * @param string $buscar    Texto a buscar dentro del nombre.
     * @param string $categoria Categoria a filtrar ('' = todas).
     * @return array<int,array{categoria:string,valor:float}> Valor por categoria.
     */
    public static function valorPorCategoria(string $buscar = '', string $categoria = ''): array
    {
        [$where, $params] = self::filtro($buscar, $categoria);
        $stmt = db()->prepare(
            "SELECT
               COALESCE(NULLIF(categoria, ''), 'Sin categoria') AS categoria,
               SUM(precio * stock)                              AS valor
             FROM productos" . $where . "
             GROUP BY COALESCE(NULLIF(categoria, ''), 'Sin categoria')
             ORDER BY valor DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Unidades de stock agrupadas por categoria.
     *
     * Alimenta el grafico de PASTEL del tablero. Muestra la proporcion de
     * piezas fisicas (no de dinero) que hay en cada categoria.
     *
     * @param string $buscar    Texto a buscar dentro del nombre.
     * @param string $categoria Categoria a filtrar ('' = todas).
     * @return array<int,array{categoria:string,unidades:int}> Unidades por categoria.
     */
    public static function stockPorCategoria(string $buscar = '', string $categoria = ''): array
    {
        [$where, $params] = self::filtro($buscar, $categoria);
        $stmt = db()->prepare(
            "SELECT
               COALESCE(NULLIF(categoria, ''), 'Sin categoria') AS categoria,
               SUM(stock)                                       AS unidades
             FROM productos" . $where . "
             GROUP BY COALESCE(NULLIF(categoria, ''), 'Sin categoria')
             ORDER BY unidades DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta el total de productos sin aplicar ningun filtro.
     *
     * Se usa para mostrar "Mostrando X de Y productos" cuando hay filtro activo,
     * dando contexto de cuantos registros existen en total.
     *
     * @return int Total de productos en la tabla.
     */
    public static function contarTodos(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM productos')->fetchColumn();
    }

    // =========================================================================
    //  CRUD (Alta, Baja, Modificacion)
    // =========================================================================

    /**
     * Inserta un producto nuevo en la base de datos.
     *
     * @param array<string,mixed> $d Marcadores con nombre:
     *        :nombre, :descripcion, :precio, :stock, :categoria
     * @return void
     */
    public static function crear(array $d): void
    {
        db()->prepare(
            'INSERT INTO productos (nombre, descripcion, precio, stock, categoria)
             VALUES (:nombre, :descripcion, :precio, :stock, :categoria)'
        )->execute($d);
    }

    /**
     * Actualiza los datos de un producto existente.
     *
     * Se agrega el marcador :id al arreglo antes de ejecutar para no tener
     * que pedirlo como parametro separado en el controlador.
     *
     * @param int                 $id El id del producto a modificar.
     * @param array<string,mixed> $d  Marcadores con nombre (mismos campos que crear).
     * @return void
     */
    public static function actualizar(int $id, array $d): void
    {
        $d[':id'] = $id;
        db()->prepare(
            'UPDATE productos
             SET nombre = :nombre,
                 descripcion = :descripcion,
                 precio = :precio,
                 stock = :stock,
                 categoria = :categoria
             WHERE id = :id'
        )->execute($d);
    }

    /**
     * Borra un producto por su id.
     *
     * Devuelve el numero de filas borradas para que el controlador sepa
     * si el producto existia o ya habia sido eliminado antes.
     *
     * @param int $id El id del producto a eliminar.
     * @return int    Filas borradas: 1 si se elimino, 0 si ya no existia.
     */
    public static function eliminar(int $id): int
    {
        $stmt = db()->prepare('DELETE FROM productos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    // =========================================================================
    //  VALIDACION
    // =========================================================================

    /**
     * Valida los datos de un producto y devuelve la lista de errores.
     *
     * Se valida en el modelo (no en el controlador) porque las reglas de
     * negocio pertenecen al modelo: si las reglas cambian, solo hay que
     * actualizar este metodo.
     *
     * Reglas actuales:
     *   - nombre: no puede estar vacio
     *   - precio: debe ser un numero mayor o igual a 0
     *   - stock:  debe ser un numero mayor o igual a 0
     *
     * @param array<string,mixed> $d Datos del formulario (nombre, precio, stock, ...).
     * @return array<int,string>     Lista de mensajes de error (vacia si todo es valido).
     */
    public static function validar(array $d): array
    {
        $errores = [];

        if (trim($d['nombre']) === '') {
            $errores[] = 'El nombre es obligatorio.';
        }
        if (!is_numeric($d['precio']) || $d['precio'] < 0) {
            $errores[] = 'El precio debe ser un numero valido (>= 0).';
        }
        if (!is_numeric($d['stock']) || $d['stock'] < 0) {
            $errores[] = 'El stock debe ser un numero valido (>= 0).';
        }

        return $errores;
    }
}
