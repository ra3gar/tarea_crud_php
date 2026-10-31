<?php
// editar.php — MODIFICAR producto (Update). Pagina privada.
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? $_POST['id'] ?? '';
if ($id === '' || !is_numeric($id)) { header("Location: index.php"); exit; }

$nombre = $descripcion = $precio = $stock = $categoria = '';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre']      ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio      = trim($_POST['precio']      ?? '');
    $stock       = trim($_POST['stock']       ?? '');
    $categoria   = trim($_POST['categoria']   ?? '');

    if ($nombre === '')                       $errores[] = "El nombre es obligatorio.";
    if (!is_numeric($precio) || $precio < 0)  $errores[] = "El precio debe ser un numero valido (>= 0).";
    if (!is_numeric($stock)  || $stock < 0)   $errores[] = "El stock debe ser un numero valido (>= 0).";

    if (empty($errores)) {
        $sql = "UPDATE productos SET nombre=:nombre, descripcion=:descripcion,
                       precio=:precio, stock=:stock, categoria=:categoria WHERE id=:id";
        $pdo->prepare($sql)->execute([
            ':nombre'=>$nombre, ':descripcion'=>$descripcion, ':precio'=>$precio,
            ':stock'=>$stock, ':categoria'=>$categoria, ':id'=>$id,
        ]);
        header("Location: index.php");
        exit;
    }
} else {
    $stmt = $pdo->prepare("SELECT nombre, descripcion, precio, stock, categoria FROM productos WHERE id=:id");
    $stmt->execute([':id'=>$id]);
    $p = $stmt->fetch();
    if (!$p) { header("Location: index.php"); exit; }
    $nombre=$p['nombre']; $descripcion=$p['descripcion']; $precio=$p['precio']; $stock=$p['stock']; $categoria=$p['categoria'];
}

$titulo = "Editar producto";
$navbar = true;
require __DIR__ . '/includes/header.php';
?>
<main class="shell narrow">
  <div class="page-head"><h1>Editar producto <span class="id-cell">#<?= htmlspecialchars($id) ?></span></h1><a href="index.php" class="btn btn-outline-secondary">Volver</a></div>

  <?php if (!empty($errores)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <form method="post" class="card card-body">
    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
    <div class="mb-3">
      <label class="form-label">Nombre *</label>
      <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($nombre) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Descripcion</label>
      <input type="text" name="descripcion" class="form-control" value="<?= htmlspecialchars($descripcion) ?>">
    </div>
    <div class="row">
      <div class="col mb-3">
        <label class="form-label">Precio *</label>
        <input type="number" step="0.01" min="0" name="precio" class="form-control" value="<?= htmlspecialchars($precio) ?>" required>
      </div>
      <div class="col mb-3">
        <label class="form-label">Stock *</label>
        <input type="number" min="0" name="stock" class="form-control" value="<?= htmlspecialchars($stock) ?>" required>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Categoria</label>
      <input type="text" name="categoria" class="form-control" value="<?= htmlspecialchars($categoria) ?>">
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Guardar cambios</button>
      <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </form>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
