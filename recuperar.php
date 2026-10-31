<?php
// recuperar.php — Restablecer contrasena con pregunta de seguridad (2 pasos).
require_once __DIR__ . '/config.php';

$usuario = trim($_POST['usuario'] ?? '');
$pregunta = ''; $errores = []; $mostrar_paso2 = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paso = $_POST['paso'] ?? '1';
    if ($paso === '1') {
        if ($usuario === '') { $errores[] = "Ingresa tu usuario."; }
        else {
            $stmt = $pdo->prepare("SELECT pregunta FROM usuarios WHERE usuario = :u");
            $stmt->execute([':u' => $usuario]);
            $row = $stmt->fetch();
            if ($row) { $pregunta = $row['pregunta']; $mostrar_paso2 = true; }
            else { $errores[] = "No encontramos ese usuario."; }
        }
    } elseif ($paso === '2') {
        $respuesta = trim($_POST['respuesta'] ?? '');
        $nueva  = $_POST['password']  ?? '';
        $nueva2 = $_POST['password2'] ?? '';
        $stmt = $pdo->prepare("SELECT pregunta, respuesta_hash FROM usuarios WHERE usuario = :u");
        $stmt->execute([':u' => $usuario]);
        $row = $stmt->fetch();
        if (!$row) { $errores[] = "Usuario invalido."; }
        else {
            $pregunta = $row['pregunta']; $mostrar_paso2 = true;
            if (strlen($nueva) < 6) $errores[] = "La nueva contrasena debe tener al menos 6 caracteres.";
            if ($nueva !== $nueva2) $errores[] = "Las contrasenas no coinciden.";
            if (!password_verify(mb_strtolower($respuesta), $row['respuesta_hash'])) $errores[] = "La respuesta de seguridad no es correcta.";
            if (empty($errores)) {
                $pdo->prepare("UPDATE usuarios SET password_hash = :ph WHERE usuario = :u")
                    ->execute([':ph' => password_hash($nueva, PASSWORD_DEFAULT), ':u' => $usuario]);
                header("Location: login.php?reset=1"); exit;
            }
        }
    }
}
$titulo = "Recuperar contrasena"; $navbar = false;
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

    <?php if (!empty($errores)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <?php if (!$mostrar_paso2): ?>
      <form method="post" class="card card-body">
        <input type="hidden" name="paso" value="1">
        <div class="mb-3"><label class="form-label">Tu usuario</label><input type="text" name="usuario" class="form-control" value="<?= htmlspecialchars($usuario) ?>" required autofocus></div>
        <button type="submit" class="btn btn-primary w-100">Continuar</button>
        <a href="login.php" class="d-block text-center mt-3" style="font-size:.86rem;">Volver al login</a>
      </form>
    <?php else: ?>
      <form method="post" class="card card-body">
        <input type="hidden" name="paso" value="2">
        <input type="hidden" name="usuario" value="<?= htmlspecialchars($usuario) ?>">
        <div class="mb-3"><label class="form-label">Tu pregunta de seguridad</label><p class="fw-semibold mb-0"><?= htmlspecialchars($pregunta) ?></p></div>
        <div class="mb-3"><label class="form-label">Respuesta</label><input type="text" name="respuesta" class="form-control" required autofocus></div>
        <div class="mb-3"><label class="form-label">Nueva contrasena</label><input type="password" name="password" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Repetir nueva contrasena</label><input type="password" name="password2" class="form-control" required></div>
        <button type="submit" class="btn btn-primary w-100">Cambiar contrasena</button>
        <a href="login.php" class="d-block text-center mt-3" style="font-size:.86rem;">Volver al login</a>
      </form>
    <?php endif; ?>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
