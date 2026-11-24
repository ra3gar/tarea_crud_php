<?php
/**
 * Vista: formulario para editar un producto existente
 *
 * Variables recibidas del ProductoController::editar():
 *   $id         - int: ID del producto que se va a editar
 *   $datos      - array con los valores actuales del producto
 *                 (nombre, descripcion, precio, stock, categoria)
 *   $categorias - array de nombres de categoría disponibles
 *   $errores    - array de mensajes de error (vacío si no hubo envío con errores)
 */

// Configuración del layout
$titulo    = 'Editar producto'; // aparece en <title> y <h1> del header
$navbar    = true;              // muestra la barra de navegación
$menuActivo = 'productos';      // resalta "Productos" en el menú

// Cabecera compartida
require dirname(__DIR__) . '/layouts/header.php';
?>

<main class="shell narrow">

  <!-- Cabecera con el ID del producto para identificación visual rápida -->
  <div class="page-head">
    <h1>
      Editar producto
      <span class="id-cell">#<?= (int) $id ?></span>
    </h1>
    <a href="<?= url('productos') ?>" class="btn btn-outline-secondary">Volver</a>
  </div>

  <?php if ($errores): ?>
    <!-- Errores de validación del intento de guardado anterior -->
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errores as $e): ?>
          <li><?= e($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Formulario de edición: POST a /productos/{id} → ProductoController::actualizar() -->
  <form method="post" action="<?= url('productos/' . (int) $id) ?>" class="card card-body">
    <!-- Token CSRF: protege el envío contra ataques de formulario externo -->
    <?= csrf_campo() ?>

    <!-- Campos reutilizables del formulario (mismo parcial que crear.php)
         En editar, $datos ya viene pre-cargado con los valores actuales del producto -->
    <?php require __DIR__ . '/_campos.php'; ?>

    <!-- Botones de acción -->
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Guardar cambios</button>
      <a href="<?= url('productos') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </form>

</main>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
