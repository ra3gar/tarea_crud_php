<?php
/**
 * Vista: confirmación antes de eliminar un producto
 *
 * Esta pantalla intermedia evita que un clic accidental o un enlace
 * externo malicioso elimine registros sin la confirmación del usuario.
 *
 * Variables recibidas del ProductoController::confirmarEliminar():
 *   $id    - int: ID del producto a eliminar
 *   $datos - array con los datos del producto (nombre, categoria, etc.)
 */

// Configuración del layout
$titulo    = 'Eliminar producto'; // aparece en <title> del header
$navbar    = true;                // muestra la barra de navegación
$menuActivo = 'productos';        // resalta "Productos" en el menú

// Cabecera compartida
require dirname(__DIR__) . '/layouts/header.php';
?>

<main class="shell narrow">

  <div class="card card-body">
    <!-- Título con color de alerta para señalar acción destructiva -->
    <h1 class="h4" style="color:var(--bad)">Eliminar producto</h1>

    <!-- Advertencia clara de que la acción es irreversible -->
    <p class="mb-1">¿Desea eliminar este registro? Esta acción no se puede deshacer.</p>

    <!-- Nombre del producto a eliminar, para que el usuario confirme
         que es el correcto antes de proceder -->
    <p class="mb-4">
      <strong><?= e($datos['nombre']) ?></strong>
      <?php if (!empty($datos['categoria'])): ?>
        <span class="text-muted">(<?= e($datos['categoria']) ?>)</span>
      <?php endif; ?>
    </p>

    <!-- Formulario de confirmación: POST a /productos/{id}/eliminar
         → ProductoController::eliminar()
         Se usa POST para que no se pueda eliminar con un simple GET
         (por ejemplo, precargando la URL en el navegador). -->
    <form method="post" action="<?= url('productos/' . (int) $id . '/eliminar') ?>" class="d-flex gap-2">
      <!-- Token CSRF: impide que otro sitio envíe este formulario por el usuario -->
      <?= csrf_campo() ?>
      <button type="submit" class="btn btn-danger">Sí, eliminar</button>
      <a href="<?= url('productos') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </form>
  </div>

</main>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
