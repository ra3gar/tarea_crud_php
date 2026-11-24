<?php
// =====================================================================
//  app/Modules/Exportar/Views/pdf_usuarios.php
//  Plantilla HTML → PDF para el listado de usuarios del sistema.
//
//  Variables disponibles:
//    $usuarios — array de filas de Usuario::todos()
//               campos: id, nombre, apellido, correo, rol, fecha_creacion
//
//  SEGURIDAD: no se muestra ni el hash de la contraseña ni
//  la pregunta/respuesta de recuperación.
// =====================================================================

$totalUsuarios = count($usuarios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Usuarios</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .encabezado {
            background-color: #9a6212;
            color: #fff;
            padding: 12px 16px;
            margin-bottom: 12px;
        }
        .encabezado h1 { margin: 0 0 2px 0; font-size: 16px; }
        .encabezado p  { margin: 0; font-size: 9px; opacity: 0.85; }

        .aviso {
            background-color: #fff8e1;
            border-left: 3px solid #f59e0b;
            padding: 5px 8px;
            font-size: 8.5px;
            color: #78350f;
            margin-bottom: 10px;
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th {
            background-color: #9a6212;
            color: #fff;
            padding: 5px 7px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        td { padding: 4px 7px; border-bottom: 1px solid #e0e0e0; font-size: 9px; }
        tr:nth-child(even) td { background-color: #fdf6ea; }

        /* Rol: badge de color */
        .rol-admin { color: #b23b3b; font-weight: bold; }
        .rol-usuario { color: #555; }

        .pie { margin-top: 10px; font-size: 8px; color: #888; text-align: right; border-top: 1px solid #ddd; padding-top: 4px; }
    </style>
</head>
<body>

<div class="encabezado">
    <h1>Informe de Usuarios del Sistema</h1>
    <p>Generado el <?= date('d/m/Y \a \l\a\s H:i') ?> &nbsp;|&nbsp; <?= $totalUsuarios ?> usuario<?= $totalUsuarios !== 1 ? 's' : '' ?> registrado<?= $totalUsuarios !== 1 ? 's' : '' ?></p>
</div>

<!-- Aviso de confidencialidad -->
<div class="aviso">
    ⚠ Documento de uso interno. No incluye contraseñas ni datos de recuperación.
    Tratar con confidencialidad.
</div>

<table>
    <thead>
        <tr>
            <th style="width:5%">#</th>
            <th style="width:18%">Nombre</th>
            <th style="width:18%">Apellido</th>
            <th style="width:30%">Correo electrónico</th>
            <th style="width:10%">Rol</th>
            <th style="width:19%">Fecha de alta</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($usuarios)): ?>
        <tr>
            <td colspan="6" style="text-align:center; padding:12px; color:#888;">No hay usuarios registrados.</td>
        </tr>
        <?php else: ?>
        <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= htmlspecialchars($u['nombre'],   ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($u['apellido'],  ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($u['correo'],    ENT_QUOTES, 'UTF-8') ?></td>
            <td class="rol-<?= htmlspecialchars($u['rol'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars(ucfirst($u['rol']), ENT_QUOTES, 'UTF-8') ?>
            </td>
            <td><?= htmlspecialchars($u['fecha_creacion'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="pie">
    Sistema de Inventario &mdash; Informe de acceso restringido &mdash; Solo administradores.
</div>

</body>
</html>
