<?php
// =====================================================================
//  auth.php — Proteccion. Se incluye al INICIO de las paginas privadas.
//  Si no hay sesion iniciada, manda al login.
// =====================================================================
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
