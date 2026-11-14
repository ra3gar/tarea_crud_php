<?php
// editar.php — MODIFICAR producto (Update). Pagina privada.
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

// Las categorias que ya existen, para ofrecerlas en el desplegable. Es la
// misma consulta que usa index.php: una lista sin repetidos. Asi se deja
// de ensuciar los datos con "Perifericos", "perifericos" y "Perifericos "
// como si fueran tres categorias distintas.
$categorias = $pdo->query(
    "SELECT DISTINCT categoria FROM productos WHERE categoria <> '' ORDER BY categoria"
)->fetchAll(PDO::FETCH_COLUMN);

$id = $_GET['id'] ?? $_POST['id'] ?? '';
if ($id === '' || !is_numeric($id)) { header("Location: index.php"); exit; }

$nombre = $descripcion = $precio = $stock = $categoria = $categoria_nueva = '';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre']      ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio      = trim($_POST['precio']      ?? '');
    $stock       = trim($_POST['stock']       ?? '');
    // Hay dos campos para una sola columna. Si la persona escribio una
    // categoria nueva, esa gana; si no, vale la que eligio del desplegable.
    // La regla se decide ACA, en el servidor. Sin JavaScript no hay campos
    // que aparezcan y desaparezcan, asi que lo que decide es el codigo.
    $categoria_nueva = trim($_POST['categoria_nueva'] ?? '');
    $categoria   = $categoria_nueva !== '' ? $categoria_nueva : trim($_POST['categoria'] ?? '');

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
        // El aviso viaja en la sesion y no en la direccion. Si fuera
        // ?creado=1, volveria a aparecer cada vez que alguien recargue o
        // comparta el enlace. En la sesion se lee una vez y se borra.
        $_SESSION['mensaje'] = ['texto' => 'Producto actualizado.', 'tipo' => 'ok'];
        header("Location: index.php");
        exit;
    }
} else {
    $stmt = $pdo->prepare("SELECT nombre, descripcion, precio, stock, categoria FROM productos WHERE id=:id");
    $stmt->execute([':id'=>$id]);
    $p = $stmt->fetch();
    if (!$p) {
        $_SESSION['mensaje'] = ['texto' => 'Ese producto ya no existe.', 'tipo' => 'error'];
        header("Location: index.php");
        exit;
    }
    // El (string) cubre las columnas que admiten NULL en la base: sin el,
    // imprimirlas mas abajo dispara un aviso de deprecacion en PHP 8.
    $nombre=(string)$p['nombre']; $descripcion=(string)$p['descripcion'];
    $precio=$p['precio']; $stock=$p['stock']; $categoria=(string)$p['categoria'];
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
      <!-- Los dos controles van SIEMPRE visibles. La alternativa habitual
           —un campo que aparece al elegir "Otra"— necesita JavaScript, y
           este proyecto es PHP puro a proposito. Asi funciona en cualquier
           navegador y la regla de cual gana se lee en el servidor. -->
      <select name="categoria" class="form-select mb-2">
        <option value="">(Sin categoria)</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>" <?= ($categoria === $c ? 'selected' : '') ?>><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="categoria_nueva" class="form-control" placeholder="...o escribi una categoria nueva"
             value="<?= htmlspecialchars($categoria_nueva) ?>">
      <div class="form-text">Si escribis una categoria nueva, se usa esa y se ignora la elegida arriba.</div>
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Guardar cambios</button>
      <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </form>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
