<?php
// =====================================================================
//  config/config.php — De donde salen las credenciales de la base.
//  No contiene secretos: solo decide la fuente y devuelve un arreglo.
//    1) Variables de entorno (las inyecta docker-compose), o
//    2) secrets.php en la raiz (para el montaje manual).
//  Vive FUERA de public/, asi que el navegador no lo puede pedir.
// =====================================================================
if (getenv('DB_PASS') !== false) {
    return [
        'host'    => getenv('DB_HOST') ?: 'tarea_crud_mysql',
        'nombre'  => getenv('DB_NAME') ?: 'tarea_crud_db',
        'usuario' => getenv('DB_USER') ?: 'root',
        'pass'    => getenv('DB_PASS'),
    ];
}
$secrets = __DIR__ . '/../secrets.php';   // define $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS
if (is_file($secrets)) {
    require $secrets;
    return ['host' => $DB_HOST, 'nombre' => $DB_NAME, 'usuario' => $DB_USER, 'pass' => $DB_PASS];
}
die('No hay configuracion de base de datos: defini variables de entorno o crea secrets.php.');
