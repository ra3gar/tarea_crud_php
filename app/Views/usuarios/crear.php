<?php
// Vista: formulario para que el admin cree un usuario nuevo.
$titulo = 'Nuevo usuario';
$navbar = true;
$menuActivo = 'usuarios';
require __DIR__ . '/../layouts/header.php';
?>
<main class="shell narrow">
  <div class="page-head"><h1>Nuevo usuario</h1><a href="<?= url('usuarios') ?>" class="btn btn-outline-secondary">Volver</a></div>

  <?php if ($errores): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <form method="post" action="<?= url('usuarios') ?>" class="card card-body">
    <div class="row">
      <div class="col mb-3"><label class="form-label">Nombre *</label><input type="text" name="nombre" class="form-control" value="<?= e($datos['nombre']) ?>" required></div>
      <div class="col mb-3"><label class="form-label">Apellido *</label><input type="text" name="apellido" class="form-control" value="<?= e($datos['apellido']) ?>" required></div>
    </div>
    <div class="mb-3"><label class="form-label">Usuario (para iniciar sesion) *</label><input type="text" name="usuario" class="form-control" value="<?= e($datos['usuario']) ?>" required></div>
    <div class="mb-3">
      <label class="form-label">Rol *</label>
      <select name="rol" class="form-select">
        <option value="usuario" <?= $datos['rol'] === 'usuario' ? 'selected' : '' ?>>usuario (uso normal)</option>
        <option value="admin"   <?= $datos['rol'] === 'admin'   ? 'selected' : '' ?>>admin (gestiona usuarios)</option>
      </select>
    </div>
    <div class="row">
      <div class="col mb-3"><label class="form-label">Contrasena *</label><input type="password" name="password" class="form-control" required></div>
      <div class="col mb-3"><label class="form-label">Repetir *</label><input type="password" name="password2" class="form-control" required></div>
    </div>
    <div class="mb-3">
      <label class="form-label">Pregunta de seguridad *</label>
      <select name="pregunta" class="form-select" required>
        <option value="">-- Elegi una --</option>
        <?php foreach ($preguntas as $q): ?>
          <option value="<?= e($q) ?>" <?= ($datos['pregunta'] === $q ? 'selected' : '') ?>><?= e($q) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3"><label class="form-label">Respuesta *</label><input type="text" name="respuesta" class="form-control" required></div>
    <button type="submit" class="btn btn-primary">Crear usuario</button>
  </form>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
