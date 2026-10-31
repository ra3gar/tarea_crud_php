<?php
// test_conexion.php — archivo TEMPORAL para verificar la Fase 2.
require_once __DIR__ . '/config.php';   // trae $pdo ya conectado

// Consulta simple para probar conexion + driver + red + credenciales.
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM productos");
$fila = $stmt->fetch();

echo "<h1>Conexion OK &#10004;</h1>";
echo "Conectado a la base <b>tarea_crud_db</b> en el host <b>mysql_global</b>.<br>";
echo "Productos en la tabla: <b>" . $fila['total'] . "</b>";
