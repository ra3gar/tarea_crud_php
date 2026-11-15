<?php
// Vista: pantalla de confirmacion antes de eliminar un producto.
$titulo = 'Eliminar producto';
$navbar = true;
$menuActivo = 'productos';   // resalta Productos en el menu
require __DIR__ . '/../layouts/header.php';
?>
<main class="shell narrow">
  <div class="card card-body">
    <h1 class="h4" style="color:var(--bad)">Eliminar producto</h1>
    <p class="mb-1">&iquest;Desea eliminar este registro? Esta accion no se puede deshacer.</p>
    <p class="mb-4"><strong><?= e($datos['nombre']) ?></strong>
      <?php if (!empty($datos['categoria'])): ?><span class="text-muted">(<?= e($datos['categoria']) ?>)</span><?php endif; ?>
    </p>
    <!-- POST a /productos/{id}/eliminar; el router lo lleva a eliminar(). -->
    <form method="post" action="<?= url('productos/' . (int) $id . '/eliminar') ?>" class="d-flex gap-2">
      <button type="submit" class="btn btn-danger">Si, eliminar</button>
      <a href="<?= url('productos') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </form>
  </div>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
