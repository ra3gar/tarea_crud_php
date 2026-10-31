<?php
// =====================================================================
//  config.php — Conexion a MySQL con PDO. NO contiene secretos.
//  Toma las credenciales de:
//    1) Variables de entorno (las inyecta docker-compose), o
//    2) secrets.php (para el setup manual, sin compose).
// =====================================================================
if (getenv('DB_PASS') !== false) {
    // --- Opcion 1: variables de entorno (docker-compose) ---
    $DB_HOST = getenv('DB_HOST') ?: 'mysql_global';
    $DB_NAME = getenv('DB_NAME') ?: 'tarea_crud_db';
    $DB_USER = getenv('DB_USER') ?: 'root';
    $DB_PASS = getenv('DB_PASS');
} else {
    // --- Opcion 2: archivo local secrets.php (setup manual) ---
    require_once __DIR__ . '/secrets.php';   // define $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS
}

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $opciones);
} catch (PDOException $e) {
    die("Error de conexion a la base de datos: " . $e->getMessage());
}
