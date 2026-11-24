<?php
/**
 * Vista: formulario para crear un nuevo producto
 *
 * Variables recibidas del ProductoController::crear():
 *   $datos      - array con valores vacíos (nombre, descripcion, precio, stock, categoria)
 *   $categorias - array de nombres de categoría disponibles
 *   $errores    - array de mensajes de error (vacío si no hubo envío previo)
 */

// Configuración del layout
$titulo    = 'Nuevo producto'; // aparece en <title> y <h1> del header
$navbar    = true;             // muestra la barra de navegación
$menuActivo = 'productos';     // resalta "Productos" en el menú

// Cabecera compartida: Bootstrap, barra de navegación, etc.
require dirname(__DIR__, 2) . '/Views/layouts/header.php';
?>

<main class="shell narrow">

  <!-- Cabecera de la página con botón de retroceso -->
  <div class="page-head">
    <h1>Nuevo producto</h1>
    <a href="<?= url('productos') ?>" class="btn btn-outline-secondary">Volver</a>
  </div>

  <?php if ($errores): ?>
    <!-- Bloque de errores de validación: se muestra solo si el formulario
         fue enviado con datos incorrectos (el controlador los cargó en $errores) -->
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errores as $e): ?>
          <li><?= e($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Formulario de creación: POST a /productos → ProductoController::guardar() -->
  <form method="post" action="<?= url('productos') ?>" class="card card-body">
    <!-- Token CSRF: evita que un sitio externo envíe el formulario por el usuario -->
    <?= csrf_campo() ?>

    <!-- Campos del formulario (nombre, descripcion, precio, stock, categoria)
         están en el parcial _campos.php para evitar duplicación con editar.php -->
    <?php require __DIR__ . '/_campos.php'; ?>

    <!-- Botones de acción -->
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Guardar producto</button>
      <a href="<?= url('productos') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </form>

</main>

<?php require dirname(__DIR__, 2) . '/Views/layouts/footer.php'; ?>
