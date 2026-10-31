<?php
// login.php — Iniciar sesion.
session_start();
require_once __DIR__ . '/config.php';
if (isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit; }

$usuario = ''; $errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $pass    = $_POST['password']     ?? '';
    if ($usuario === '' || $pass === '') {
        $errores[] = "Completa usuario y contrasena.";
    } else {
        $stmt = $pdo->prepare("SELECT id, usuario, password_hash FROM usuarios WHERE usuario = :u");
        $stmt->execute([':u' => $usuario]);
        $u = $stmt->fetch();
        if ($u && password_verify($pass, $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $u['id'];
            $_SESSION['usuario']    = $u['usuario'];
            header("Location: index.php"); exit;
        } else {
            $errores[] = "Usuario o contrasena incorrectos.";
        }
    }
}
$titulo = "Iniciar sesion"; $navbar = false;
require __DIR__ . '/includes/header.php';
?>
<main class="auth-wrap">
  <div class="auth-card">
    <div class="auth-brand">
      <div class="brand">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M12 2 3 7v10l9 5 9-5V7z"/><path d="M3 7l9 5 9-5"/><path d="M12 12v10"/></svg>
        Inventario
      </div>
    </div>

    <?php if (isset($_GET['registrado'])): ?><div class="alert alert-success">Cuenta creada. Ya podes iniciar sesion.</div><?php endif; ?>
    <?php if (isset($_GET['reset'])): ?><div class="alert alert-success">Contrasena actualizada. Inicia sesion con la nueva.</div><?php endif; ?>
    <?php if (!empty($errores)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <form method="post" class="card card-body">
      <div class="mb-3">
        <label class="form-label">Usuario</label>
        <input type="text" name="usuario" class="form-control" value="<?= htmlspecialchars($usuario) ?>" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Contrasena</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Entrar</button>
      <div class="d-flex justify-content-between mt-3" style="font-size:.86rem;">
        <a href="registro.php">Crear cuenta</a>
        <a href="recuperar.php">Olvide mi contrasena</a>
      </div>
    </form>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
