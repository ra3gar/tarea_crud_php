<?php
// Vista del formulario de registro (alta de cuenta): nombre y apellido
// separados, usuario (login), contrasena y pregunta de seguridad.
$titulo = 'Crear cuenta';
$navbar = false;
require __DIR__ . '/../layouts/header.php';
?>
<main class="auth-wrap">
  <div class="auth-card" style="max-width:460px;">
      <div class="auth-brand">
        <div class="brand">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M12 2 3 7v10l9 5 9-5V7z"/><path d="M3 7l9 5 9-5"/><path d="M12 12v10"/></svg>
          Inventario
        </div>
      </div>
    <?php if ($errores): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" action="<?= url('registro') ?>" class="card card-body">
      <!-- Nombre y apellido separados: evita meter el nombre completo en el login. -->
      <div class="row">
        <div class="col mb-3"><label class="form-label">Nombre *</label><input type="text" name="nombre" class="form-control" value="<?= e($nombre) ?>" required></div>
        <div class="col mb-3"><label class="form-label">Apellido *</label><input type="text" name="apellido" class="form-control" value="<?= e($apellido) ?>" required></div>
      </div>
      <div class="mb-3"><label class="form-label">Usuario (para iniciar sesion) *</label><input type="text" name="usuario" class="form-control" value="<?= e($usuario) ?>" required></div>
      <div class="row">
        <div class="col mb-3"><label class="form-label">Contrasena *</label><input type="password" name="password" class="form-control" required></div>
        <div class="col mb-3"><label class="form-label">Repetir *</label><input type="password" name="password2" class="form-control" required></div>
      </div>
      <div class="mb-3">
        <label class="form-label">Pregunta de seguridad *</label>
        <select name="pregunta" class="form-select" required>
          <option value="">-- Elegi una --</option>
          <?php foreach ($preguntas as $q): ?>
            <option value="<?= e($q) ?>" <?= ($pregunta === $q ? 'selected' : '') ?>><?= e($q) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3"><label class="form-label">Respuesta *</label><input type="text" name="respuesta" class="form-control" required></div>
      <button type="submit" class="btn btn-primary w-100">Registrarme</button>
      <a href="<?= url('login') ?>" class="d-block text-center mt-3" style="font-size:.86rem;">Ya tengo cuenta</a>
    </form>
  </div>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
