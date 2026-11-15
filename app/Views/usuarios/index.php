<?php
// Listado de usuarios (solo admin). Todo texto sale por e() (regla 3).
$titulo = 'Usuarios';
$navbar = true;
$menuActivo = 'usuarios';
require __DIR__ . '/../layouts/header.php';
?>
<main class="shell">
  <div class="page-head">
    <h1>Usuarios</h1>
    <a href="<?= url('usuarios/crear') ?>" class="btn btn-primary">+ Nuevo usuario</a>
  </div>

  <div class="panel">
    <table class="inv">
      <thead>
        <tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Alta</th><th class="num">Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
        <tr>
          <td class="id-cell">#<?= (int) $u['id'] ?></td>
          <td>
            <?= e($u['usuario']) ?>
            <?php if ((int) $u['id'] === $yoId): ?><span class="pill ok">vos</span><?php endif; ?>
          </td>
          <td><?= e(trim($u['nombre'] . ' ' . $u['apellido'])) ?></td>
          <td><span class="pill <?= $u['rol'] === 'admin' ? 'warn' : '' ?>"><?= e($u['rol']) ?></span></td>
          <td class="text-muted"><?= !empty($u['fecha_creacion']) ? date('d/m/Y', strtotime($u['fecha_creacion'])) : '' ?></td>
          <td class="num">
            <div class="actions">
              <a href="<?= url('usuarios/' . (int) $u['id'] . '/editar') ?>" class="btn btn-sm btn-warning">Editar</a>
              <?php if ((int) $u['id'] !== $yoId): ?>
                <!-- A vos mismo no te aparece "Eliminar"; igual el controlador
                     bloquea autoeliminarse y borrar al ultimo admin. -->
                <form method="post" action="<?= url('usuarios/' . (int) $u['id'] . '/eliminar') ?>">
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
<?php require __DIR__ . '/../layouts/footer.php'; ?>
