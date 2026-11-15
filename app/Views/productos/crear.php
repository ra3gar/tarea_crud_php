<?php
// Vista: formulario para crear un producto. Reusa los campos de _campos.php.
$titulo = 'Nuevo producto';
$navbar = true;
$menuActivo = 'productos';   // resalta Productos en el menu
require __DIR__ . '/../layouts/header.php';
?>
<main class="shell narrow">
  <div class="page-head"><h1>Nuevo producto</h1><a href="<?= url('productos') ?>" class="btn btn-outline-secondary">Volver</a></div>

  <?php if ($errores): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <!-- El formulario manda POST a /productos; el router lo lleva a guardar(). -->
  <form method="post" action="<?= url('productos') ?>" class="card card-body">
    <?php require __DIR__ . '/_campos.php'; ?>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Guardar producto</button>
      <a href="<?= url('productos') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </form>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
