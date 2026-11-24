<?php
/**
 * Vista: formulario para crear un nuevo usuario (solo administradores)
 *
 * Variables recibidas del UsuarioController::crear():
 *   $datos     - array con valores vacíos para pre-cargar el formulario
 *                (usuario, nombre, apellido, rol, pregunta)
 *   $preguntas - array de strings con las preguntas de seguridad disponibles
 *   $errores   - array de mensajes de error (vacío si no hubo envío previo)
 */

// Configuración del layout
$titulo    = 'Nuevo usuario';
$navbar    = true;
$menuActivo = 'usuarios';

// Cabecera compartida
require dirname(__DIR__, 2) . '/Views/layouts/header.php';
?>

<main class="shell narrow">

  <!-- Cabecera con botón de retroceso -->
  <div class="page-head">
    <h1>Nuevo usuario</h1>
    <a href="<?= url('usuarios') ?>" class="btn btn-outline-secondary">Volver</a>
  </div>

  <?php if ($errores): ?>
    <!-- Lista de errores de validación -->
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errores as $e): ?>
          <li><?= e($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Formulario de alta: POST a /usuarios → UsuarioController::guardar() -->
  <form method="post" action="<?= url('usuarios') ?>" class="card card-body">
    <!-- Token CSRF: evita envíos desde sitios externos -->
    <?= csrf_campo() ?>

    <!-- Nombre y Apellido: en dos columnas -->
    <div class="row">
      <div class="col mb-3">
        <label class="form-label">Nombre *</label>
        <input
          type="text"
          name="nombre"
          class="form-control"
          value="<?= e($datos['nombre']) ?>"
          required
        >
      </div>
      <div class="col mb-3">
        <label class="form-label">Apellido *</label>
        <input
          type="text"
          name="apellido"
          class="form-control"
          value="<?= e($datos['apellido']) ?>"
          required
        >
      </div>
    </div>

    <!-- Nombre de usuario (login): único en el sistema -->
    <div class="mb-3">
      <label class="form-label">Usuario (para iniciar sesión) *</label>
      <input
        type="text"
        name="usuario"
        class="form-control"
        value="<?= e($datos['usuario']) ?>"
        required
      >
    </div>

    <!-- Selector de rol: usuario normal o administrador -->
    <div class="mb-3">
      <label class="form-label">Rol *</label>
      <select name="rol" class="form-select">
        <!-- usuario: acceso normal al inventario, sin gestión de cuentas -->
        <option value="usuario" <?= $datos['rol'] === 'usuario' ? 'selected' : '' ?>>
          usuario (uso normal)
        </option>
        <!-- admin: acceso total, incluye gestión de usuarios -->
        <option value="admin" <?= $datos['rol'] === 'admin' ? 'selected' : '' ?>>
          admin (gestiona usuarios)
        </option>
      </select>
    </div>

    <!-- Contraseña: se pide dos veces para evitar errores de escritura.
         Se hashea con bcrypt en el controlador; nunca se guarda en texto plano. -->
    <div class="row">
      <div class="col mb-3">
        <label class="form-label">Contraseña *</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="col mb-3">
        <label class="form-label">Repetir *</label>
        <input type="password" name="password2" class="form-control" required>
      </div>
    </div>

    <!-- Pregunta de seguridad: se usa para recuperar la contraseña sin email -->
    <div class="mb-3">
      <label class="form-label">Pregunta de seguridad *</label>
      <select name="pregunta" class="form-select" required>
        <option value="">-- Elige una --</option>
        <?php foreach ($preguntas as $q): ?>
          <option
            value="<?= e($q) ?>"
            <?= ($datos['pregunta'] === $q ? 'selected' : '') ?>
          ><?= e($q) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Respuesta de seguridad: se guarda en minúsculas + bcrypt -->
    <div class="mb-3">
      <label class="form-label">Respuesta *</label>
      <input type="text" name="respuesta" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Crear usuario</button>
  </form>

</main>

<?php require dirname(__DIR__, 2) . '/Views/layouts/footer.php'; ?>
