<?php
// eliminar.php — BORRAR producto (Delete), con confirmacion. Pagina privada.
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? $_POST['id'] ?? '';
if ($id === '' || !is_numeric($id)) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare("DELETE FROM productos WHERE id=:id")->execute([':id'=>$id]);
    // Igual que en crear y editar: el aviso va por la sesion, se muestra
    // una sola vez y no queda pegado en la direccion.
    $_SESSION['mensaje'] = ['texto' => 'Producto eliminado.', 'tipo' => 'ok'];
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT nombre, categoria FROM productos WHERE id=:id");
$stmt->execute([':id'=>$id]);
$p = $stmt->fetch();
if (!$p) {
    // Antes esto redirigia en silencio y la persona no se enteraba de
    // por que no paso nada. Ahora avisa, y con tipo 'error' para que se
    // pinte distinto de una confirmacion.
    $_SESSION['mensaje'] = ['texto' => 'Ese producto ya no existe.', 'tipo' => 'error'];
    header("Location: index.php");
    exit;
}

$titulo = "Eliminar producto";
$navbar = true;
require __DIR__ . '/includes/header.php';
?>
<main class="shell narrow">
  <div class="card card-body">
    <h1 class="h4" style="color:var(--bad)">Eliminar producto</h1>
    <p class="mb-1">&iquest;Desea eliminar este registro? Esta accion no se puede deshacer.</p>
    <p class="mb-4"><strong><?= htmlspecialchars($p['nombre']) ?></strong>
      <?php if (!empty($p['categoria'])): ?><span class="text-muted">(<?= htmlspecialchars($p['categoria']) ?>)</span><?php endif; ?>
    </p>
    <form method="post" class="d-flex gap-2">
      <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
      <button type="submit" class="btn btn-danger">Si, eliminar</button>
      <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
    </form>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
