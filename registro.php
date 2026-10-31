<?php
// registro.php — Crear usuario (contrasena y respuesta HASHEADAS).
require_once __DIR__ . '/config.php';

$preguntas = [
    'Cual es el nombre de tu primera mascota?',
    'En que ciudad naciste?',
    'Cual es el nombre de tu primera escuela?',
    'Cual es el segundo nombre de tu mama?',
];
$usuario = ''; $pregunta = ''; $errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario   = trim($_POST['usuario']   ?? '');
    $pass      = $_POST['password']        ?? '';
    $pass2     = $_POST['password2']       ?? '';
    $pregunta  = $_POST['pregunta']        ?? '';
    $respuesta = trim($_POST['respuesta']  ?? '');

    if ($usuario === '')                   $errores[] = "El usuario es obligatorio.";
    if (strlen($pass) < 6)                 $errores[] = "La contrasena debe tener al menos 6 caracteres.";
    if ($pass !== $pass2)                  $errores[] = "Las contrasenas no coinciden.";
    if (!in_array($pregunta, $preguntas))  $errores[] = "Elegi una pregunta de seguridad.";
    if ($respuesta === '')                 $errores[] = "La respuesta de seguridad es obligatoria.";

    if (empty($errores)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :u");
        $stmt->execute([':u' => $usuario]);
        if ($stmt->fetch()) $errores[] = "Ese usuario ya esta registrado. Elegi otro.";
    }
    if (empty($errores)) {
        $sql = "INSERT INTO usuarios (usuario, password_hash, pregunta, respuesta_hash)
                VALUES (:usuario, :ph, :pregunta, :rh)";
        $pdo->prepare($sql)->execute([
            ':usuario'=>$usuario,
            ':ph'=>password_hash($pass, PASSWORD_DEFAULT),
            ':pregunta'=>$pregunta,
            ':rh'=>password_hash(mb_strtolower($respuesta), PASSWORD_DEFAULT),
        ]);
        header("Location: login.php?registrado=1"); exit;
    }
}
$titulo = "Crear cuenta"; $navbar = false;
require __DIR__ . '/includes/header.php';
?>
<main class="auth-wrap">
  <div class="auth-card" style="max-width:460px;">
    <div class="auth-brand">
      <div class="brand">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M12 2 3 7v10l9 5 9-5V7z"/><path d="M3 7l9 5 9-5"/><path d="M12 12v10"/></svg>
        Inventario
      </div>
    </div>

    <?php if (!empty($errores)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <form method="post" class="card card-body">
      <div class="mb-3">
        <label class="form-label">Usuario *</label>
        <input type="text" name="usuario" class="form-control" value="<?= htmlspecialchars($usuario) ?>" required>
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
            <option value="<?= htmlspecialchars($q) ?>" <?= ($pregunta === $q ? 'selected' : '') ?>><?= htmlspecialchars($q) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Respuesta *</label>
        <input type="text" name="respuesta" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Registrarme</button>
      <a href="login.php" class="d-block text-center mt-3" style="font-size:.86rem;">Ya tengo cuenta</a>
    </form>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
