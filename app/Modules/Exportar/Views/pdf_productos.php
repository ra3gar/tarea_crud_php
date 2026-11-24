<?php
// =====================================================================
//  app/Modules/Exportar/Views/pdf_productos.php
//  Plantilla HTML que Dompdf convierte a PDF.
//
//  Variables disponibles (inyectadas por PdfExporter::generar()):
//    $productos — array de filas del inventario, mismo formato que
//                 Producto::todos() devuelve.
//
//  REGLAS de esta plantilla:
//    - Sin navbar, sin botones, sin menús → solo el contenido del informe.
//    - Todo texto de usuario pasa por htmlspecialchars() (función e() o
//      la función nativa) para prevenir XSS incluso en el PDF.
//    - Dompdf soporta CSS 2.1 parcialmente; usar float, flexbox o grid
//      puede no funcionar. Se prefiere tablas HTML para el layout.
//    - charset UTF-8 obligatorio para acentos en español.
// =====================================================================

// Calcular totales para el resumen del pie de informe
$totalProductos = count($productos);
$totalUnidades  = array_sum(array_column($productos, 'stock'));
$valorTotal     = array_sum(
    array_map(fn($p) => (float)$p['precio'] * (int)$p['stock'], $productos)
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de Productos</title>
    <style>
        /* ---- Hoja y tipografía ---- */
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #111;
            margin: 0;
            padding: 0;
        }

        /* ---- Encabezado del informe ---- */
        .encabezado {
            background-color: #2f5fe0;
            color: #fff;
            padding: 12px 16px;
            margin-bottom: 12px;
        }
        .encabezado h1 {
            margin: 0 0 2px 0;
            font-size: 16px;
            letter-spacing: 0.5px;
        }
        .encabezado p {
            margin: 0;
            font-size: 9px;
            opacity: 0.85;
        }

        /* ---- Tabla de productos ---- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        th {
            background-color: #2f5fe0;
            color: #fff;
            padding: 5px 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        td {
            padding: 4px 6px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 9px;
            vertical-align: top;
        }
        /* Filas alternadas para facilitar la lectura */
        tr:nth-child(even) td {
            background-color: #eef2ff;
        }
        /* Columna de precio: alineada a la derecha */
        td.precio, th.precio {
            text-align: right;
        }
        /* Columna de stock: centrada */
        td.stock, th.stock {
            text-align: center;
        }
        /* Alerta de stock bajo */
        .bajo {
            color: #c0392b;
            font-weight: bold;
        }

        /* ---- Resumen al pie ---- */
        .resumen {
            background-color: #f0f4ff;
            border: 1px solid #c5cfed;
            padding: 8px 10px;
            margin-top: 4px;
        }
        .resumen table {
            margin: 0;
        }
        .resumen td {
            border: none;
            padding: 2px 10px 2px 0;
            background: none;
            font-size: 9px;
        }
        .resumen .etiqueta {
            color: #555;
        }
        .resumen .valor {
            font-weight: bold;
            color: #2f5fe0;
        }

        /* ---- Pie de página ---- */
        .pie {
            margin-top: 10px;
            font-size: 8px;
            color: #888;
            text-align: right;
            border-top: 1px solid #ddd;
            padding-top: 4px;
        }
    </style>
</head>
<body>

<!-- ================================================================
     ENCABEZADO DEL INFORME
     ================================================================ -->
<div class="encabezado">
    <h1>Informe de Inventario — Productos</h1>
    <p>Generado el <?= date('d/m/Y \a \l\a\s H:i') ?> &nbsp;|&nbsp; Total: <?= $totalProductos ?> producto<?= $totalProductos !== 1 ? 's' : '' ?></p>
</div>

<!-- ================================================================
     TABLA DE PRODUCTOS
     ================================================================ -->
<table>
    <thead>
        <tr>
            <th style="width:5%">#</th>
            <th style="width:22%">Nombre</th>
            <th style="width:28%">Descripción</th>
            <th class="precio" style="width:12%">Precio</th>
            <th class="stock" style="width:8%">Stock</th>
            <th style="width:15%">Categoría</th>
            <th style="width:10%">Alta</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($productos)): ?>
        <tr>
            <td colspan="7" style="text-align:center; padding:12px; color:#888;">
                No hay productos registrados en el inventario.
            </td>
        </tr>
        <?php else: ?>
        <?php foreach ($productos as $p): ?>
        <tr>
            <td><?= (int)$p['id'] ?></td>
            <td><?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($p['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td class="precio">$<?= number_format((float)$p['precio'], 2) ?></td>
            <td class="stock <?= (int)$p['stock'] < 10 ? 'bajo' : '' ?>">
                <?= (int)$p['stock'] ?>
                <?= (int)$p['stock'] < 10 ? ' ⚠' : '' ?>
            </td>
            <td><?= htmlspecialchars($p['categoria'] ?? 'Sin categoría', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($p['fecha_creacion'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- ================================================================
     RESUMEN / TOTALES
     ================================================================ -->
<div class="resumen">
    <table>
        <tr>
            <td class="etiqueta">Total de productos:</td>
            <td class="valor"><?= $totalProductos ?></td>
            <td class="etiqueta" style="padding-left:20px">Total de unidades en stock:</td>
            <td class="valor"><?= number_format($totalUnidades) ?></td>
            <td class="etiqueta" style="padding-left:20px">Valor total del inventario:</td>
            <td class="valor">$<?= number_format($valorTotal, 2) ?></td>
        </tr>
    </table>
</div>

<!-- ================================================================
     PIE DE PÁGINA
     ================================================================ -->
<div class="pie">
    Sistema de Inventario &mdash; Documento generado automáticamente. No requiere firma.
</div>

</body>
</html>
