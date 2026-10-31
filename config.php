<?php
// =====================================================================
//  config.php — Conexion a MySQL con PDO. NO contiene secretos.
//  Las credenciales viven en secrets.php (que NO se sube a git).
//  Este archivo lo incluyen TODAS las paginas que usan la base.
// =====================================================================

// Trae las variables $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS
require_once __DIR__ . '/secrets.php';

// DSN = "cadena de conexion": driver + host + base + codificacion.
// OJO: el host es mysql_global (el contenedor), NO localhost.
$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

// Opciones de PDO (buenas practicas):
$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // ante un error, lanza excepcion (facil de ver)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // resultados como arreglos asociativos ($fila['nombre'])
    PDO::ATTR_EMULATE_PREPARES   => false,                 // prepared statements REALES de MySQL (mas seguro)
];

try {
    // $pdo es el objeto de conexion que reutilizaremos en todo el proyecto.
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $opciones);
} catch (PDOException $e) {
    // Si falla, detener con un mensaje claro.
    die("Error de conexion a la base de datos: " . $e->getMessage());
}
