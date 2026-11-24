<?php
// =====================================================================
//  app/Modules/Auth/Views/login.php — Formulario de inicio de sesion.
//
//  Variables que espera recibir del AuthController::login() / autenticar():
//    $errores  array<string>  Lista de errores de validacion (vacia si ninguno)
//    $usuario  string         Lo que el usuario escribio en el campo usuario
//                             (para no obligarlo a escribirlo de nuevo)
//
//  Rutas de layout:
//    El header y footer estan en app/Views/layouts/ (compartidos entre modulos).
//    Desde este archivo, la ruta relativa es: dirname(__DIR__) . '/layouts/'
//
//  Por que $navbar = false:
//    El login no tiene sesion activa, asi que no tiene sentido mostrar
//    el menu de navegacion. El header lo oculta cuando $navbar es false.
// =====================================================================

// Titulo de la pestana del navegador
$titulo = 'Iniciar sesion';

// false = el header no muestra la barra de navegacion (sin sesion activa)
$navbar = false;

// Incluir el encabezado compartido
require dirname(__DIR__) . '/layouts/header.php';
?>
<main class="auth-wrap">
  <div class="auth-card">

    <!-- Logo / nombre del sistema -->
    <div class="auth-brand">
      <div class="brand">
        <!-- Icono de caja/paquete (SVG inline: no necesita archivo externo) -->
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round">
          <path d="M12 2 3 7v10l9 5 9-5V7z"/>
          <path d="M3 7l9 5 9-5"/>
          <path d="M12 12v10"/>
        </svg>
        Inventario
      </div>
    </div>

    <!-- Lista de errores (solo se muestra si hay algun error) -->
    <?php if ($errores): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errores as $e): ?>
            <!-- e() escapa el texto para evitar XSS antes de imprimirlo en HTML -->
            <li><?= e($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Formulario de login -->
    <form method="post" action="<?= url('login') ?>" class="card card-body">

      <!-- Campo oculto con el token CSRF (lo valida index.php antes del controlador) -->
      <?= csrf_campo() ?>

      <div class="mb-3">
        <label class="form-label">Usuario</label>
        <!-- value conserva lo que el usuario escribio si hubo error -->
        <input type="text" name="usuario" class="form-control"
               value="<?= e($usuario) ?>" required autofocus>
      </div>

      <div class="mb-3">
        <label class="form-label">Contrasena</label>
        <!-- La contrasena NO se conserva por seguridad: no se debe exponer en HTML -->
        <input type="password" name="password" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">Entrar</button>

      <!-- Links secundarios: registro y recuperacion -->
      <div class="d-flex justify-content-between mt-3" style="font-size:.86rem;">
        <a href="<?= url('registro') ?>">Crear cuenta</a>
        <a href="<?= url('recuperar') ?>">Olvide mi contrasena</a>
      </div>

    </form>
  </div>
</main>

<?php
// Pie de pagina compartido (cierra el <body> y el <html>)
require dirname(__DIR__) . '/layouts/footer.php';
?>
