<?php
// Cabecera comun. Antes de incluir, la pagina puede definir:
//   $titulo  (texto del <title>)   $navbar (true para mostrar la barra superior)
$titulo = $titulo ?? 'Inventario';
$navbar = $navbar ?? false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titulo) ?></title>
  <!-- Todo local: no depende de internet -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/fonts/fuentes.css" rel="stylesheet">
  <link href="assets/css/estilo.css" rel="stylesheet">
</head>
<body>
<?php if ($navbar): ?>
  <header class="topbar">
    <div class="topbar-inner">
      <a href="index.php" class="brand">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M12 2 3 7v10l9 5 9-5V7z"/><path d="M3 7l9 5 9-5"/><path d="M12 12v10"/></svg>
        Inventario
      </a>
      <div class="topbar-user">
        <span class="user-chip">Hola, <b><?= htmlspecialchars($_SESSION['usuario'] ?? '') ?></b></span>
        <a href="logout.php" class="btn-ghost">Cerrar sesion</a>
      </div>
    </div>
  </header>
<?php endif; ?>
