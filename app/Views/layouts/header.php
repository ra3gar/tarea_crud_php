<?php
// Cabecera comun a todas las paginas. La vista define antes $titulo y, si
// quiere la barra superior, $navbar = true.
$titulo = $titulo ?? 'Inventario';
$navbar = $navbar ?? false;
// Que item del menu resaltar. Cada pagina lo fija antes de incluir la
// cabecera (ej: $menuActivo = 'productos'). Si no lo fija, no se resalta nada.
$menuActivo = $menuActivo ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?></title>
  <!-- Todo local y con base_path(): funciona aunque el sitio este en una
       subcarpeta. El ?v= es la fecha del archivo, para que el navegador
       no muestre una version vieja del CSS en cache. -->
  <link href="<?= asset('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= asset('assets/fonts/fuentes.css') ?>" rel="stylesheet">
  <link href="<?= asset('assets/css/estilo.css') ?>?v=<?= @filemtime(__DIR__ . '/../../../public/assets/css/estilo.css') ?>" rel="stylesheet">
</head>
<body>
<?php if (!empty($_SESSION['mensaje'])):
    $msj = $_SESSION['mensaje']; unset($_SESSION['mensaje']);  // se muestra una sola vez
?>
  <div class="aviso aviso-<?= ($msj['tipo'] === 'error' ? 'error' : 'ok') ?>"><?= e($msj['texto']) ?></div>
<?php endif; ?>
<?php if ($navbar): ?>
  <header class="topbar">
    <div class="topbar-inner">
      <!-- El logo tambien es el enlace a Productos (la portada). -->
      <a href="<?= url('productos') ?>" class="brand">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M12 2 3 7v10l9 5 9-5V7z"/><path d="M3 7l9 5 9-5"/><path d="M12 12v10"/></svg>
        Inventario
      </a>
      <!-- Menu unico, igual en todas las paginas. Cada item es un enlace con
           un icono + su texto. Los iconos son SVG de una sola etiqueta (como
           el logo): sin libreria y sin JavaScript. La clase 'activa' se agrega
           solo al item de la pagina actual, usando $menuActivo. -->
      <nav class="topbar-user">
        <a href="<?= url('productos') ?>" class="btn-ghost <?= $menuActivo === 'productos' ? 'activa' : '' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M21 8V5l-9-3-9 3v3"/><path d="M3 8l9 3 9-3"/><path d="M3 8v8l9 3 9-3V8"/><path d="M12 11v10"/></svg>
          Productos
        </a>
        <a href="<?= url('categorias') ?>" class="btn-ghost <?= $menuActivo === 'categorias' ? 'activa' : '' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M20.6 13.4 12 22l-9-9V4a1 1 0 0 1 1-1h8z"/><circle cx="7.5" cy="7.5" r="1.3" fill="currentColor" stroke="none"/></svg>
          Categorias
        </a>
        <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
        <!-- Solo los admin ven y usan Usuarios. -->
        <a href="<?= url('usuarios') ?>" class="btn-ghost <?= $menuActivo === 'usuarios' ? 'activa' : '' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Usuarios
        </a>
        <?php endif; ?>
        <span class="user-chip">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
          Hola, <b><?= e($_SESSION['nombre'] ?? $_SESSION['usuario'] ?? '') ?></b>
        </span>
        <a href="<?= url('logout') ?>" class="btn-ghost">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
          Cerrar sesion
        </a>
      </nav>
    </div>
  </header>
<?php endif; ?>
