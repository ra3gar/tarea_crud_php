<?php
/**
 * Layout: cabecera compartida de todas las páginas
 *
 * Este archivo es incluido al inicio de cada vista.
 * Las vistas deben definir estas variables ANTES de incluirlo:
 *
 *   $titulo     (string) Texto que aparece en la pestaña del navegador.
 *   $navbar     (bool)   true = mostrar la barra de navegación superior.
 *   $menuActivo (string) Slug del ítem a resaltar en el menú:
 *                        'productos', 'categorias', 'usuarios'
 *
 * Si alguna no está definida, se usa el valor predeterminado.
 */

// Valor predeterminado del título si la vista no lo define
$titulo = $titulo ?? 'Inventario';

// Por defecto no se muestra la barra (páginas de login, registro, etc.)
$navbar = $navbar ?? false;

// Ítem activo en el menú; '' = ninguno resaltado
$menuActivo = $menuActivo ?? '';

// Ruta al CSS para el query-string de caché-busting (?v=timestamp)
// filemtime() devuelve la fecha de modificación del archivo; así el
// navegador recarga el CSS automáticamente cuando cambia.
$cssMtime = @filemtime(__DIR__ . '/../../../public/assets/css/estilo.css');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?></title>

  <!--
    Los assets se sirven desde /public/assets/ mediante asset().
    Usar base_path() o asset() (definidas en funciones.php) garantiza que
    los enlaces funcionen aunque el sitio esté en una subcarpeta del servidor.
    El parámetro ?v=TIMESTAMP fuerza al navegador a recargar el CSS
    cuando el archivo cambia, evitando servir una versión cacheada antigua.
  -->
  <link href="<?= asset('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= asset('assets/fonts/fuentes.css') ?>" rel="stylesheet">
  <link href="<?= asset('assets/css/estilo.css') ?>?v=<?= $cssMtime ?>" rel="stylesheet">
</head>
<body>

<?php
// ----------------------------------------------------------------
// MENSAJE FLASH (toast/aviso temporal)
// Se guarda en sesión por el controlador y se muestra una sola vez.
// Después de mostrarlo se elimina de la sesión (unset) para que no
// vuelva a aparecer en el próximo request.
// ----------------------------------------------------------------
if (!empty($_SESSION['mensaje'])):
    $msj = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']); // consumir el mensaje: se ve una sola vez
?>
  <div class="aviso aviso-<?= ($msj['tipo'] === 'error' ? 'error' : 'ok') ?>">
    <?= e($msj['texto']) ?>
  </div>
<?php endif; ?>

<?php if ($navbar): ?>
<!--
  BARRA DE NAVEGACIÓN SUPERIOR
  Solo se muestra en páginas donde el usuario ya inició sesión ($navbar = true).
  Las páginas de login, registro y recuperación tienen $navbar = false.
-->
<header class="topbar">
  <div class="topbar-inner">

    <!-- Logo / enlace a la página principal (Productos) -->
    <a href="<?= url('productos') ?>" class="brand">
      <!--
        Icono de caja/paquete hecho en SVG inline:
        no depende de ninguna librería de iconos y nunca genera
        una petición HTTP adicional.
      -->
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="1.8" stroke-linejoin="round">
        <path d="M12 2 3 7v10l9 5 9-5V7z"/>
        <path d="M3 7l9 5 9-5"/>
        <path d="M12 12v10"/>
      </svg>
      Inventario
    </a>

    <!-- Menú de navegación principal -->
    <nav class="topbar-user">

      <!-- Ítem: Productos -->
      <a href="<?= url('productos') ?>"
         class="btn-ghost <?= $menuActivo === 'productos' ? 'activa' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.8" stroke-linejoin="round">
          <path d="M21 8V5l-9-3-9 3v3"/>
          <path d="M3 8l9 3 9-3"/>
          <path d="M3 8v8l9 3 9-3V8"/>
          <path d="M12 11v10"/>
        </svg>
        Productos
      </a>

      <!-- Ítem: Categorías -->
      <a href="<?= url('categorias') ?>"
         class="btn-ghost <?= $menuActivo === 'categorias' ? 'activa' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.8" stroke-linejoin="round">
          <path d="M20.6 13.4 12 22l-9-9V4a1 1 0 0 1 1-1h8z"/>
          <circle cx="7.5" cy="7.5" r="1.3" fill="currentColor" stroke="none"/>
        </svg>
        Categorías
      </a>

      <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
      <!--
        Ítem: Usuarios (solo visible para administradores)
        La verificación de $_SESSION['rol'] impide que usuarios normales
        vean el enlace; el Router también bloquea el acceso a las rutas.
      -->
      <a href="<?= url('usuarios') ?>"
         class="btn-ghost <?= $menuActivo === 'usuarios' ? 'activa' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.8" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        Usuarios
      </a>
      <?php endif; ?>

      <!-- Chip de usuario: muestra el nombre del usuario conectado -->
      <span class="user-chip">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.8" stroke-linejoin="round">
          <circle cx="12" cy="8" r="4"/>
          <path d="M4 21a8 8 0 0 1 16 0"/>
        </svg>
        Hola, <b><?= e($_SESSION['nombre'] ?? $_SESSION['usuario'] ?? '') ?></b>
      </span>

      <!-- Enlace de cierre de sesión -->
      <a href="<?= url('logout') ?>" class="btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.8" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <path d="M16 17l5-5-5-5"/>
          <path d="M21 12H9"/>
        </svg>
        Cerrar sesión
      </a>

    </nav>
  </div>
</header>
<?php endif; ?>
