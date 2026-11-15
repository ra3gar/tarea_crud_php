<?php
namespace App\Core;

use PDO;
use PDOException;

// =====================================================================
//  app/Core/Database.php — La conexion a la base, una sola para toda la app.
//  La primera vez que alguien pide la conexion, la crea; despues devuelve
//  siempre la misma. Asi no se abren diez conexiones por peticion.
// =====================================================================

/**
 * Conexion unica a la base de datos (patron "singleton").
 *
 * Guarda el PDO en una propiedad estatica: se crea una sola vez y se reutiliza
 * en toda la peticion. Los modelos llaman a Database::pdo() para consultar.
 */
class Database
{
    /** @var PDO|null Conexion compartida. Empieza en null y se crea al primer uso. */
    private static ?PDO $pdo = null;

    /**
     * Devuelve la conexion PDO, creandola la primera vez que se la pide.
     *
     * Lee las credenciales de config/config.php (que las saca de variables de
     * entorno o de secrets.php) y arma el PDO con modo de errores por excepcion.
     *
     * @return PDO Conexion lista para preparar y ejecutar consultas.
     */
    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $cfg = require __DIR__ . '/../../config/config.php';
            $dsn = "mysql:host={$cfg['host']};dbname={$cfg['nombre']};charset=utf8mb4";
            try {
                self::$pdo = new PDO($dsn, $cfg['usuario'], $cfg['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Sin emulacion: un marcador con nombre no se puede repetir
                    // en una misma consulta. Importa al armar filtros.
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                die('Error de conexion a la base de datos: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
