<?php
// Campos del formulario de producto. Lo usan crear y editar, asi el HTML del
// formulario esta en un solo lugar. $datos trae los valores actuales;
// $categorias, la lista de la tabla de categorias.
// Si el producto tiene una categoria que ya no esta en la lista, la mostramos
// igual para no perderla.
$catActual = $datos['categoria'] ?? '';
$opciones = $categorias;
if ($catActual !== '' && !in_array($catActual, $opciones, true)) {
    array_unshift($opciones, $catActual);
}
?>
<div class="mb-3">
  <label class="form-label">Nombre *</label>
  <input type="text" name="nombre" class="form-control" value="<?= e($datos['nombre']) ?>" required>
</div>
<div class="mb-3">
  <label class="form-label">Descripcion</label>
  <input type="text" name="descripcion" class="form-control" value="<?= e($datos['descripcion']) ?>">
</div>
<div class="row">
  <div class="col mb-3">
    <label class="form-label">Precio *</label>
    <input type="number" step="0.01" min="0" name="precio" class="form-control" value="<?= e((string) $datos['precio']) ?>" required>
  </div>
  <div class="col mb-3">
    <label class="form-label">Stock *</label>
    <input type="number" min="0" name="stock" class="form-control" value="<?= e((string) $datos['stock']) ?>" required>
  </div>
</div>
<div class="mb-3">
  <label class="form-label">Categoria</label>
  <!-- Se elige de la lista (tabla categorias). Para agregar una nueva, se usa
       la ventana de Categorias. Asi no se escribe a mano y no se ensucia. -->
  <select name="categoria" class="form-select">
    <option value="">(Sin categoria)</option>
    <?php foreach ($opciones as $c): ?>
      <option value="<?= e($c) ?>" <?= ($catActual === $c ? 'selected' : '') ?>><?= e($c) ?></option>
    <?php endforeach; ?>
  </select>
</div>
