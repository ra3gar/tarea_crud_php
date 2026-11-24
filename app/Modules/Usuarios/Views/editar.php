<?php
/**
 * Vista: editar perfil de usuario y resetear contraseña (solo administradores)
 *
 * Variables recibidas del UsuarioController::editar():
 *   $u       - array con los datos del usuario a editar
 *              (id, usuario, nombre, apellido, rol)
 *   $errores - array de mensajes de error (vacío si no hubo envío con errores)
 *
 * Esta vista contiene DOS formularios:
 *   1. Editar nombre, apellido y rol
 *   2. Resetear contraseña (formulario separado por seguridad)
 */

// Configuración del layout
$titulo    = 'Editar usuario';
$navbar    = true;
$menuActivo = 'usuarios';

// Cabecera compartida
require dirname(__DIR__, 2) . '/Views/layouts/header.php';
?>

<main class="shell narrow">

  <!-- Cabecera con botón de retroceso -->
  <div class="page-head">
    <h1>Editar usuario</h1>
    <a href="<?= url('usuarios') ?>" class="btn btn-outline-secondary">Volver</a>
  </div>

  <?php if ($errores): ?>
    <!-- Errores de validación del formulario de perfil -->
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errores as $e): ?>
          <li><?= e($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- ============================================================
       FORMULARIO 1: Datos de perfil y rol
       El nombre de usuario (login) NO se puede cambiar aquí; se muestra
       como campo deshabilitado solo para referencia visual.
       ============================================================ -->
  <form method="post" action="<?= url('usuarios/' . (int) $u['id']) ?>" class="card card-body mb-3">
    <?= csrf_campo() ?>

    <!-- Nombre de usuario: solo lectura (disabled no se envía con el formulario) -->
    <div class="mb-3">
      <label class="form-label">Usuario (login)</label>
      <input
        type="text"
        class="form-control"
        value="<?= e($u['usuario']) ?>"
        disabled
      >
      <div class="form-text">El nombre de usuario no se puede cambiar.</div>
    </div>

    <!-- Nombre y Apellido: en dos columnas -->
    <div class="row">
      <div class="col mb-3">
        <label class="form-label">Nombre *</label>
        <input
          type="text"
          name="nombre"
          class="form-control"
          value="<?= e($u['nombre']) ?>"
          required
        >
      </div>
      <div class="col mb-3">
        <label class="form-label">Apellido *</label>
        <input
          type="text"
          name="apellido"
          class="form-control"
          value="<?= e($u['apellido']) ?>"
          required
        >
      </div>
    </div>

    <!-- Selector de rol -->
    <div class="mb-3">
      <label class="form-label">Rol *</label>
      <select name="rol" class="form-select">
        <option value="usuario" <?= $u['rol'] === 'usuario' ? 'selected' : '' ?>>
          usuario (uso normal)
        </option>
        <option value="admin" <?= $u['rol'] === 'admin' ? 'selected' : '' ?>>
          admin (gestiona usuarios)
        </option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Guardar cambios</button>
  </form>

  <!-- ============================================================
       FORMULARIO 2: Resetear contraseña
       Separado del formulario de perfil por seguridad: así el admin
       puede cambiar la contraseña sin modificar accidentalmente el perfil
       y viceversa. La contraseña actual nunca se muestra.
       ============================================================ -->
  <form method="post" action="<?= url('usuarios/' . (int) $u['id'] . '/password') ?>" class="card card-body">
    <?= csrf_campo() ?>
    <label class="form-label fw-semibold">Resetear contraseña</label>
    <p class="text-muted small mb-3">
      Establece una nueva contraseña para este usuario.
      La contraseña actual no se muestra ni se requiere.
    </p>
    <div class="row">
      <div class="col mb-3">
        <input
          type="password"
          name="password"
          class="form-control"
          placeholder="Nueva contraseña"
          required
        >
      </div>
      <div class="col mb-3">
        <input
          type="password"
          name="password2"
          class="form-control"
          placeholder="Repetir"
          required
        >
      </div>
    </div>
    <button type="submit" class="btn btn-outline-secondary">Cambiar contraseña</button>
  </form>

</main>

<?php require dirname(__DIR__, 2) . '/Views/layouts/footer.php'; ?>
