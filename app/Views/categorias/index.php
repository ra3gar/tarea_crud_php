<?php
// Vista de la ventana de categorias: lista + formulario para agregar. Solo
// arma HTML con lo que paso el controlador; no consulta la base.
$titulo = 'Categorias';
$navbar = true;
$menuActivo = 'categorias';   // resalta Categorias en el menu
require __DIR__ . '/../layouts/header.php';
?>
<main class="shell narrow">
  <div class="page-head"><h1>Categorias</h1></div>

  <form method="post" action="<?= url('categorias') ?>" class="card card-body mb-3">
    <label class="form-label">Nueva categoria</label>
    <div class="d-flex gap-2">
      <input type="text" name="nombre" class="form-control" placeholder="Ej: Cables" required>
      <button type="submit" class="btn btn-primary">Agregar</button>
    </div>
  </form>

  <?php if (!$categorias): ?>
    <div class="alert alert-info">Todavia no hay categorias.</div>
  <?php else: ?>
  <div class="panel">
    <table class="inv">
      <thead><tr><th>Categoria</th><th class="num">Accion</th></tr></thead>
      <tbody>
        <?php foreach ($categorias as $c): ?>
        <tr>
          <td><?= e($c['nombre']) ?></td>
          <td class="num">
            <form method="post" action="<?= url('categorias/' . (int) $c['id'] . '/eliminar') ?>">
              <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
