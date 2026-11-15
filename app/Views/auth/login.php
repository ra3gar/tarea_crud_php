<?php
// Vista del formulario de inicio de sesion. Recibe $errores y $usuario.
$titulo = 'Iniciar sesion';
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
    <form method="post" action="<?= url('login') ?>" class="card card-body">
      <div class="mb-3"><label class="form-label">Usuario</label><input type="text" name="usuario" class="form-control" value="<?= e($usuario) ?>" required autofocus></div>
      <div class="mb-3"><label class="form-label">Contrasena</label><input type="password" name="password" class="form-control" required></div>
      <button type="submit" class="btn btn-primary w-100">Entrar</button>
      <div class="d-flex justify-content-between mt-3" style="font-size:.86rem;">
        <a href="<?= url('registro') ?>">Crear cuenta</a>
        <a href="<?= url('recuperar') ?>">Olvide mi contrasena</a>
      </div>
    </form>
  </div>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
