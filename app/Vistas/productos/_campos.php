<?php
/**
 * Parcial: campos del formulario de producto
 *
 * Este archivo es un fragmento reutilizable (partial) que incluyen
 * tanto crear.php como editar.php. Centraliza el HTML del formulario
 * en un solo lugar para no duplicar código.
 *
 * Variables que necesita el archivo padre:
 *   $datos      - array con los valores actuales del producto
 *                 (vacíos en crear, pre-cargados en editar)
 *   $categorias - array de strings con los nombres de categoría
 *                 que existen en la base de datos
 */

// Si el producto tiene una categoría que ya no está en la lista
// (por ejemplo, fue eliminada después), la incluimos igual al principio
// para no perder ese valor y no romper el <select>.
$catActual = $datos['categoria'] ?? '';
$opciones  = $categorias;

if ($catActual !== '' && !in_array($catActual, $opciones, true)) {
    // Poner la categoría huérfana al inicio para que se vea y se conserve
    array_unshift($opciones, $catActual);
}
?>

<!-- Campo Nombre: requerido, texto libre -->
<div class="mb-3">
  <label class="form-label">Nombre *</label>
  <input
    type="text"
    name="nombre"
    class="form-control"
    value="<?= e($datos['nombre']) ?>"
    required
  >
</div>

<!-- Campo Descripción: opcional -->
<div class="mb-3">
  <label class="form-label">Descripción</label>
  <input
    type="text"
    name="descripcion"
    class="form-control"
    value="<?= e($datos['descripcion']) ?>"
  >
</div>

<!-- Precio y Stock: en dos columnas lado a lado -->
<div class="row">
  <div class="col mb-3">
    <label class="form-label">Precio *</label>
    <!-- step="0.01" permite centavos; min="0" evita precios negativos -->
    <input
      type="number"
      step="0.01"
      min="0"
      name="precio"
      class="form-control"
      value="<?= e((string) $datos['precio']) ?>"
      required
    >
  </div>
  <div class="col mb-3">
    <label class="form-label">Stock *</label>
    <!-- min="0" evita cantidades negativas -->
    <input
      type="number"
      min="0"
      name="stock"
      class="form-control"
      value="<?= e((string) $datos['stock']) ?>"
      required
    >
  </div>
</div>

<!-- Selector de Categoría
     La categoría se elige de las existentes en la tabla `categorias`.
     Para agregar una nueva categoría se usa la sección Categorías,
     así se evita escribir a mano y se mantiene la integridad de los datos. -->
<div class="mb-3">
  <label class="form-label">Categoría</label>
  <select name="categoria" class="form-select">
    <option value="">(Sin categoría)</option>
    <?php foreach ($opciones as $c): ?>
      <option
        value="<?= e($c) ?>"
        <?= ($catActual === $c ? 'selected' : '') ?>
      ><?= e($c) ?></option>
    <?php endforeach; ?>
  </select>
</div>
