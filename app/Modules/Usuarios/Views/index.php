<?php
/**
 * Vista: listado de usuarios del sistema (solo para administradores)
 *
 * Variables recibidas del UsuarioController::index():
 *   $usuarios - array de filas con todos los usuarios
 *               (id, usuario, nombre, apellido, rol, fecha_creacion)
 *   $yoId     - int: ID del usuario actualmente conectado
 *               (para evitar que se muestre el botón "Eliminar" propio)
 *
 * Todo texto de usuario pasa por e() para prevenir XSS.
 */

// Configuración del layout
$titulo    = 'Usuarios';   // aparece en <title>
$navbar    = true;         // muestra la barra de navegación
$menuActivo = 'usuarios'; // resalta "Usuarios" en el menú

// Cabecera compartida: Bootstrap, barra de navegación, estilos
require dirname(__DIR__, 2) . '/Views/layouts/header.php';
?>

<main class="shell">

  <!-- ============================================================
       CABECERA DE PÁGINA: título + botones de acción
       ============================================================ -->
  <div class="page-head">
    <h1>Usuarios</h1>
    <div class="page-head-actions no-print">
      <!-- Botón para agregar un usuario nuevo -->
      <a href="<?= url('usuarios/crear') ?>" class="btn btn-primary">+ Nuevo usuario</a>

      <!-- Descargar listado de usuarios en PDF (solo admin, igual que esta vista) -->
      <a href="<?= url('exportar/usuarios/pdf') ?>"
         class="btn btn-outline-danger no-print"
         title="Descargar usuarios como PDF">
        📄 PDF
      </a>

      <!-- Descargar listado de usuarios en Excel -->
      <a href="<?= url('exportar/usuarios/excel') ?>"
         class="btn btn-outline-success no-print"
         title="Descargar usuarios como Excel (.xlsx)">
        📊 Excel
      </a>

      <!-- Impresión rápida del navegador -->
      <button onclick="window.print()" class="btn btn-outline-secondary no-print" title="Imprimir listado">
        🖨️ Imprimir
      </button>
    </div>
  </div>

  <!-- ============================================================
       TABLA DE USUARIOS
       ============================================================ -->
  <div class="panel">
    <table class="inv">
      <thead>
        <tr>
          <th>ID</th>
          <th>Usuario</th>
          <th>Nombre</th>
          <th>Rol</th>
          <th>Alta</th>
          <!-- La columna de acciones se oculta en impresión -->
          <th class="num no-print">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
        <tr>
          <td class="id-cell">#<?= (int) $u['id'] ?></td>
          <td>
            <?= e($u['usuario']) ?>
            <?php if ((int) $u['id'] === $yoId): ?>
              <!-- Etiqueta visual para identificar al usuario conectado -->
              <span class="pill ok">tú</span>
            <?php endif; ?>
          </td>
          <td><?= e(trim($u['nombre'] . ' ' . $u['apellido'])) ?></td>
          <td>
            <!-- Píldora de color: amarillo para admin, neutro para usuario regular -->
            <span class="pill <?= $u['rol'] === 'admin' ? 'warn' : '' ?>">
              <?= e($u['rol']) ?>
            </span>
          </td>
          <td class="text-muted">
            <?= !empty($u['fecha_creacion'])
                ? date('d/m/Y', strtotime($u['fecha_creacion']))
                : '' ?>
          </td>
          <!-- Acciones: ocultas en impresión con la clase no-print -->
          <td class="num no-print">
            <div class="actions">
              <a href="<?= url('usuarios/' . (int) $u['id'] . '/editar') ?>"
                 class="btn btn-sm btn-warning">Editar</a>
              <?php if ((int) $u['id'] !== $yoId): ?>
                <!-- El botón "Eliminar" NO aparece para el propio usuario.
                     Además el controlador tiene sus propios candados:
                     1) no puedes eliminarte a ti mismo
                     2) no puedes eliminar al último admin -->
                <form method="post" action="<?= url('usuarios/' . (int) $u['id'] . '/eliminar') ?>">
                  <?= csrf_campo() ?>
                  <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</main>

<?php require dirname(__DIR__, 2) . '/Views/layouts/footer.php'; ?>
