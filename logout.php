<?php
// =====================================================================
//  logout.php — Cerrar sesion.
// =====================================================================
session_start();
$_SESSION = [];          // vaciar datos de sesion
session_destroy();       // destruir la sesion
header("Location: login.php");
exit;
