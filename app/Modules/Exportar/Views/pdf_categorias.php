<?php
// =====================================================================
//  app/Modules/Exportar/Views/pdf_categorias.php
//  Plantilla HTML → PDF para el listado de categorías.
//
//  Variables disponibles (inyectadas por PdfExporter::generar()):
//    $categorias — array de filas con claves: id, nombre, total_productos.
//                  total_productos viene de la consulta con JOIN en
//                  ExportarController::categoriasConConteo().
// =====================================================================

$totalCategorias = count($categorias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Categorías</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .encabezado {
            background-color: #157f52;
            color: #fff;
            padding: 12px 16px;
            margin-bottom: 12px;
        }
        .encabezado h1 { margin: 0 0 2px 0; font-size: 16px; }
        .encabezado p  { margin: 0; font-size: 9px; opacity: 0.85; }

        table { width: 60%; border-collapse: collapse; margin-bottom: 14px; }
        th {
            background-color: #157f52;
            color: #fff;
            padding: 5px 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        td { padding: 4px 8px; border-bottom: 1px solid #e0e0e0; font-size: 9px; }
        tr:nth-child(even) td { background-color: #e9f7f1; }
        td.centro { text-align: center; }
        .cero { color: #888; font-style: italic; }

        .pie { margin-top: 10px; font-size: 8px; color: #888; text-align: right; border-top: 1px solid #ddd; padding-top: 4px; }
    </style>
</head>
<body>

<div class="encabezado">
    <h1>Informe de Categorías</h1>
    <p>Generado el <?= date('d/m/Y \a \l\a\s H:i') ?> &nbsp;|&nbsp; <?= $totalCategorias ?> categoría<?= $totalCategorias !== 1 ? 's' : '' ?> registrada<?= $totalCategorias !== 1 ? 's' : '' ?></p>
</div>

<table>
    <thead>
        <tr>
            <th style="width:10%">#</th>
            <th style="width:60%">Categoría</th>
            <th class="centro" style="width:30%">Productos</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($categorias)): ?>
        <tr>
            <td colspan="3" style="text-align:center; padding:12px; color:#888;">No hay categorías registradas.</td>
        </tr>
        <?php else: ?>
        <?php foreach ($categorias as $c): ?>
        <tr>
            <td><?= (int)$c['id'] ?></td>
            <td><?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="centro <?= (int)$c['total_productos'] === 0 ? 'cero' : '' ?>">
                <?= (int)$c['total_productos'] === 0 ? 'Sin productos' : (int)$c['total_productos'] ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="pie">
    Sistema de Inventario &mdash; Documento generado automáticamente.
</div>

</body>
</html>
