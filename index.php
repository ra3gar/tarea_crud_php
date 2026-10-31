<?php
// index.php — LISTAR productos (Read). Pagina privada.
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

$productos = $pdo->query(
  "SELECT id, nombre, descripcion, precio, stock, categoria FROM productos ORDER BY id"
)->fetchAll();

// Metricas para la franja superior.
$total_prod = count($productos);
$total_unidades = 0; $bajo = 0;
foreach ($productos as $p) {
    $total_unidades += (int)$p['stock'];
    if ((int)$p['stock'] < 10) $bajo++;
}

$titulo = "Inventario de productos";
$navbar = true;
require __DIR__ . '/includes/header.php';
?>
<main class="shell">
  <div class="page-head">
    <h1>Productos</h1>
    <a href="crear.php" class="btn btn-primary">+ Nuevo producto</a>
  </div>

  <div class="stats">
    <div class="stat">      <div class="n"><?= $total_prod ?></div>                 <div class="l">Productos</div></div>
    <div class="stat ok">   <div class="n"><?= number_format($total_unidades) ?></div><div class="l">Unidades en stock</div></div>
    <div class="stat warn"> <div class="n"><?= $bajo ?></div>                      <div class="l">Con stock bajo (&lt;10)</div></div>
  </div>

  <?php if ($total_prod === 0): ?>
    <div class="alert alert-info">Todavia no hay productos. Agrega el primero con "+ Nuevo producto".</div>
  <?php else: ?>
  <div class="panel">
    <table class="inv">
      <thead>
        <tr>
          <th>ID</th><th>Nombre</th><th>Descripcion</th>
          <th class="num">Precio</th><th class="num">Stock</th><th>Categoria</th><th class="num">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($productos as $p):
            $s = (int)$p['stock'];
            if     ($s <= 0) { $clase = 'bad';  $texto = 'Sin stock'; }
            elseif ($s < 10) { $clase = 'warn'; $texto = $s . ' u'; }
            else             { $clase = 'ok';   $texto = $s . ' u'; }
        ?>
        <tr>
          <td class="id-cell">#<?= $p['id'] ?></td>
          <td><?= htmlspecialchars($p['nombre']) ?></td>
          <td class="text-muted"><?= htmlspecialchars($p['descripcion']) ?></td>
          <td class="num">$<?= number_format($p['precio'], 2) ?></td>
          <td class="num"><span class="pill <?= $clase ?>"><?= $texto ?></span></td>
          <td><?= htmlspecialchars($p['categoria']) ?></td>
          <td>
            <div class="actions">
              <a href="editar.php?id=<?= $p['id'] ?>"  class="btn btn-sm btn-warning">Editar</a>
              <a href="eliminar.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger">Eliminar</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
