<?php
// Vista de recuperacion de contrasena en DOS pasos: paso 1 pide el usuario;
// paso 2 muestra la pregunta y pide la respuesta + la nueva clave. $paso2 decide
// cual de los dos formularios se muestra.
$titulo = 'Recuperar contrasena';
$navbar = false;
require __DIR__ . '/../layouts/header.php';
?>
<main class="auth-wrap">
  <div class="auth-card">
      <div class="auth-brand">
        <div class="brand">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M12 2 3 7v10l9 5 9-5V7z"/><path d="M3 7l9 5 9-5"/><path d="M12 12v10"/></svg>
          Inventario
        </div>
      </div>
    <?php if ($errores): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if (!$paso2): ?>
      <form method="post" action="<?= url('recuperar') ?>" class="card card-body">
        <input type="hidden" name="paso" value="1">
        <div class="mb-3"><label class="form-label">Tu usuario</label><input type="text" name="usuario" class="form-control" value="<?= e($usuario) ?>" required autofocus></div>
        <button type="submit" class="btn btn-primary w-100">Continuar</button>
        <a href="<?= url('login') ?>" class="d-block text-center mt-3" style="font-size:.86rem;">Volver al login</a>
      </form>
    <?php else: ?>
      <form method="post" action="<?= url('recuperar') ?>" class="card card-body">
        <input type="hidden" name="paso" value="2">
        <input type="hidden" name="usuario" value="<?= e($usuario) ?>">
        <div class="mb-3"><label class="form-label">Tu pregunta de seguridad</label><p class="fw-semibold mb-0"><?= e($pregunta) ?></p></div>
        <div class="mb-3"><label class="form-label">Respuesta</label><input type="text" name="respuesta" class="form-control" required autofocus></div>
        <div class="mb-3"><label class="form-label">Nueva contrasena</label><input type="password" name="password" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Repetir nueva contrasena</label><input type="password" name="password2" class="form-control" required></div>
        <button type="submit" class="btn btn-primary w-100">Cambiar contrasena</button>
        <a href="<?= url('login') ?>" class="d-block text-center mt-3" style="font-size:.86rem;">Volver al login</a>
      </form>
    <?php endif; ?>
  </div>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
