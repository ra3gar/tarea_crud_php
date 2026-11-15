<?php
// Vista: editar un usuario (perfil + rol) y resetear su contrasena. Solo admin.
$titulo = 'Editar usuario';
$navbar = true;
$menuActivo = 'usuarios';
require __DIR__ . '/../layouts/header.php';
?>
<main class="shell narrow">
  <div class="page-head"><h1>Editar usuario</h1><a href="<?= url('usuarios') ?>" class="btn btn-outline-secondary">Volver</a></div>

  <?php if ($errores): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <!-- Datos de perfil y rol. El login (usuario) no se edita aca. -->
  <form method="post" action="<?= url('usuarios/' . (int) $u['id']) ?>" class="card card-body mb-3">
    <div class="mb-3"><label class="form-label">Usuario (login)</label><input type="text" class="form-control" value="<?= e($u['usuario']) ?>" disabled></div>
    <div class="row">
      <div class="col mb-3"><label class="form-label">Nombre *</label><input type="text" name="nombre" class="form-control" value="<?= e($u['nombre']) ?>" required></div>
      <div class="col mb-3"><label class="form-label">Apellido *</label><input type="text" name="apellido" class="form-control" value="<?= e($u['apellido']) ?>" required></div>
    </div>
    <div class="mb-3">
      <label class="form-label">Rol *</label>
      <select name="rol" class="form-select">
        <option value="usuario" <?= $u['rol'] === 'usuario' ? 'selected' : '' ?>>usuario (uso normal)</option>
        <option value="admin"   <?= $u['rol'] === 'admin'   ? 'selected' : '' ?>>admin (gestiona usuarios)</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Guardar cambios</button>
  </form>

  <!-- Reseteo de contrasena: formulario aparte. Nunca se muestra la actual. -->
  <form method="post" action="<?= url('usuarios/' . (int) $u['id'] . '/password') ?>" class="card card-body">
    <label class="form-label">Resetear contrasena</label>
    <div class="row">
      <div class="col mb-3"><input type="password" name="password" class="form-control" placeholder="Nueva contrasena" required></div>
      <div class="col mb-3"><input type="password" name="password2" class="form-control" placeholder="Repetir" required></div>
    </div>
    <button type="submit" class="btn btn-outline-secondary">Cambiar contrasena</button>
  </form>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
