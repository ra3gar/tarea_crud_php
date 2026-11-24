<?php
/**
 * Vista: listado de categorías + formulario para agregar una nueva
 *
 * Variables recibidas del CategoriaController::index():
 *   $categorias - array de filas [id, nombre] con todas las categorías existentes
 *
 * Esta vista no consulta la base de datos directamente.
 * Todo texto de usuario pasa por e() para prevenir XSS.
 */

// Configuración del layout
$titulo    = 'Categorías';    // aparece en <title> y como contexto del header
$navbar    = true;            // muestra la barra de navegación
$menuActivo = 'categorias';  // resalta "Categorías" en el menú

// Cabecera compartida: Bootstrap, barra de navegación, estilos
require dirname(__DIR__, 2) . '/Views/layouts/header.php';
?>

<main class="shell narrow">

  <!-- ============================================================
       CABECERA DE PÁGINA: título + botón de impresión
       ============================================================ -->
  <div class="page-head">
    <h1>Categorías</h1>
    <div class="page-head-actions no-print">
      <!-- Descargar listado de categorías en PDF -->
      <a href="<?= url('exportar/categorias/pdf') ?>"
         class="btn btn-outline-danger no-print"
         title="Descargar categorías como PDF">
        📄 PDF
      </a>

      <!-- Descargar listado de categorías en Excel -->
      <a href="<?= url('exportar/categorias/excel') ?>"
         class="btn btn-outline-success no-print"
         title="Descargar categorías como Excel (.xlsx)">
        📊 Excel
      </a>

      <!-- Impresión rápida del navegador -->
      <button onclick="window.print()" class="btn btn-outline-secondary no-print" title="Imprimir listado">
        🖨️ Imprimir
      </button>
    </div>
  </div>

  <!-- ============================================================
       FORMULARIO: agregar nueva categoría
       Oculto en impresión — solo los datos importan en el impreso.
       ============================================================ -->
  <form method="post" action="<?= url('categorias') ?>" class="card card-body mb-3 no-print">
    <!-- Token CSRF: evita envíos desde sitios externos -->
    <?= csrf_campo() ?>
    <label class="form-label">Nueva categoría</label>
    <div class="d-flex gap-2">
      <input
        type="text"
        name="nombre"
        class="form-control"
        placeholder="Ej: Cables"
        required
      >
      <button type="submit" class="btn btn-primary">Agregar</button>
    </div>
  </form>

  <!-- ============================================================
       TABLA DE CATEGORÍAS
       ============================================================ -->
  <?php if (!$categorias): ?>
    <!-- Estado vacío: todavía no se ha creado ninguna categoría -->
    <div class="alert alert-info">Todavía no hay categorías.</div>
  <?php else: ?>
  <div class="panel">
    <table class="inv">
      <thead>
        <tr>
          <th>Categoría</th>
          <!-- La columna de acción se oculta en impresión (no-print) -->
          <th class="num no-print">Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categorias as $c): ?>
        <tr>
          <td><?= e($c['nombre']) ?></td>
          <!-- Botón de eliminación: cada categoría tiene su propio mini-formulario
               con POST para evitar eliminaciones accidentales por GET -->
          <td class="num no-print">
            <form
              method="post"
              action="<?= url('categorias/' . (int) $c['id'] . '/eliminar') ?>"
            >
              <?= csrf_campo() ?>
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

<?php require dirname(__DIR__, 2) . '/Views/layouts/footer.php'; ?>
