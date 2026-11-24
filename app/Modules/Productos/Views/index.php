<?php
/**
 * Vista: Tablero + listado de productos
 *
 * Recibe del ProductoController estas variables:
 *   $m              - array con métricas: valor, productos, unidades, bajos
 *   $porCategoria   - array de filas [categoria, valor] para el gráfico de barras
 *   $stockPorCategoria - array de filas [categoria, unidades] para la dona
 *   $productos      - array de productos filtrados (o todos)
 *   $categorias     - array de nombres de categoría para el <select>
 *   $buscar         - string con el texto de búsqueda activo ('' si ninguno)
 *   $categoria      - string con la categoría filtrada ('' si ninguna)
 *   $hayFiltro      - bool: true si se aplicó algún filtro
 *   $totalSinFiltro - int: total de productos sin filtrar (para el mensaje)
 *
 * Esta vista SOLO arma HTML; nunca consulta la base de datos directamente.
 * Todo texto de usuario pasa por e() para prevenir XSS (regla de seguridad).
 */

// Título que mostrará el <title> y el <h1> del layout
$titulo = 'Inventario de productos';

// true activa la barra de navegación (el usuario ya inició sesión)
$navbar = true;

// Resalta el ítem "Productos" en el menú de navegación
$menuActivo = 'productos';

// Incluir la cabecera compartida (Bootstrap, barra de nav, etc.)
// dirname(__DIR__, 2) sube dos niveles: Views → Productos → Modules → app/
// Luego baja a Views/layouts/header.php
require dirname(__DIR__, 2) . '/Views/layouts/header.php';

// ----------------------------------------------------------------
// Pre-cálculo del gráfico de BARRAS (valor por categoría)
// La barra más larga representa el 100 %; las demás se escalan en %
// ----------------------------------------------------------------
$valorMayor = 0.0;
foreach ($porCategoria as $fila) {
    if ((float) $fila['valor'] > $valorMayor) {
        $valorMayor = (float) $fila['valor'];
    }
}

// ----------------------------------------------------------------
// Pre-cálculo del gráfico de DONA (unidades por categoría)
// Usamos conic-gradient puro de CSS; PHP genera las paradas del gradiente.
// ----------------------------------------------------------------

// Total de unidades: define el 100 % del pastel
$totalUnidades = 0;
foreach ($stockPorCategoria as $fila) {
    $totalUnidades += (int) $fila['unidades'];
}

// Paleta fija de colores para las tajadas.
// Si hay más categorías que colores, se repite con módulo (%).
$coloresPastel = [
    '#2f5fe0', '#157f52', '#9a6212', '#b23b3b',
    '#6250d6', '#1b9e8f', '#c2569b', '#5a6b86',
];

// Construimos en un solo recorrido:
//   $paradasPastel → array de strings "color inicio% fin%" para conic-gradient
//   $leyendaPastel → array de filas para la leyenda lateral
$paradasPastel = [];
$leyendaPastel = [];
$acumulado = 0.0;
$i = 0;

foreach ($stockPorCategoria as $fila) {
    $unidades = (int) $fila['unidades'];
    // Porcentaje que ocupa esta categoría dentro del total
    $pct   = $totalUnidades > 0 ? ($unidades / $totalUnidades) * 100 : 0;
    // Color asignado de forma circular sobre la paleta
    $color = $coloresPastel[$i % count($coloresPastel)];
    $inicio = $acumulado;
    $fin    = $acumulado + $pct;
    // Formato "color X% Y%" que entiende conic-gradient
    $paradasPastel[] = $color . ' '
        . number_format($inicio, 2, '.', '') . '% '
        . number_format($fin,    2, '.', '') . '%';
    $leyendaPastel[] = [
        'categoria' => $fila['categoria'],
        'color'     => $color,
        'pct'       => $pct,
        'unidades'  => $unidades,
    ];
    $acumulado = $fin;
    $i++;
}

// String final del gradiente, listo para insertar en el atributo style
$gradientePastel = implode(', ', $paradasPastel);
?>

<main class="shell">

  <!-- ============================================================
       CABECERA DE PÁGINA: título + botones de acción
       ============================================================ -->
  <div class="page-head">
    <h1>Productos</h1>
    <div class="page-head-actions no-print">
      <!-- Botón para agregar un producto nuevo -->
      <a href="<?= url('productos/crear') ?>" class="btn btn-primary">+ Nuevo producto</a>

      <!-- ── Botones de exportación ── -->
      <!-- PDF: descarga un informe del inventario en formato PDF.
           Usa Dompdf en el servidor; no depende del diálogo de impresión. -->
      <a href="<?= url('exportar/productos/pdf') ?>"
         class="btn btn-outline-danger no-print"
         title="Descargar inventario como PDF">
        📄 PDF
      </a>

      <!-- Excel: descarga el inventario como archivo .xlsx.
           Usa PhpSpreadsheet; se puede abrir en Excel, LibreOffice, etc. -->
      <a href="<?= url('exportar/productos/excel') ?>"
         class="btn btn-outline-success no-print"
         title="Descargar inventario como Excel (.xlsx)">
        📊 Excel
      </a>

      <!-- Impresión rápida en el navegador (ventana de impresión del SO) -->
      <button onclick="window.print()" class="btn btn-outline-secondary no-print" title="Imprimir listado">
        🖨️ Imprimir
      </button>
    </div>
  </div>

  <?php if ($hayFiltro): ?>
    <!-- Aviso visible cuando hay filtros activos: las métricas muestran
         solo el subconjunto filtrado, no el inventario completo. -->
    <p class="resumen-filtrado">
      Resumen de los productos que coinciden, no del inventario completo.
    </p>
  <?php endif; ?>

  <!-- ============================================================
       TARJETAS DE MÉTRICAS (KPI)
       Cada tarjeta tiene clase CSS para colorearla: valor, ok, warn
       ============================================================ -->
  <div class="stats">
    <div class="stat valor">
      <div class="n">$<?= number_format((float) $m['valor'], 2) ?></div>
      <div class="l">Valor del inventario</div>
    </div>
    <div class="stat">
      <div class="n"><?= (int) $m['productos'] ?></div>
      <div class="l">Productos</div>
    </div>
    <div class="stat ok">
      <div class="n"><?= number_format((int) $m['unidades']) ?></div>
      <div class="l">Unidades en stock</div>
    </div>
    <div class="stat warn">
      <div class="n"><?= (int) $m['bajos'] ?></div>
      <div class="l">Con stock bajo (&lt;10)</div>
    </div>
  </div>

  <?php if ($porCategoria || ($stockPorCategoria && $totalUnidades > 0)): ?>
  <!-- ============================================================
       GRÁFICOS DEL TABLERO (solo si hay datos)
       Se muestran lado a lado en pantallas anchas (.graficos-fila).
       En pantallas pequeñas se apilan (media query en estilo.css).
       En impresión se muestran si hay datos (ver @media print).
       ============================================================ -->
  <div class="graficos-fila">

    <?php if ($porCategoria): ?>
    <!-- Gráfico 1: VALOR por categoría (barras horizontales)
         Responde la pregunta "¿dónde está mi dinero?" -->
    <div class="panel grafico">
      <h2 class="grafico-titulo">Valor del inventario por categoría</h2>
      <?php foreach ($porCategoria as $fila):
          // Escalar el ancho: la categoría con más valor = 100%
          $ancho = $valorMayor > 0
              ? ((float) $fila['valor'] / $valorMayor) * 100
              : 0;
      ?>
        <div class="barra-fila">
          <div class="barra-etiqueta"><?= e($fila['categoria']) ?></div>
          <div class="barra-pista">
            <!-- El ancho es un número PHP; se fuerza a formato seguro
                 antes de colocarlo en el atributo style (sin XSS) -->
            <div class="barra" style="width: <?= number_format($ancho, 2, '.', '') ?>%"></div>
          </div>
          <div class="barra-valor">$<?= number_format((float) $fila['valor'], 2) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($stockPorCategoria && $totalUnidades > 0): ?>
    <!-- Gráfico 2: UNIDADES por categoría (dona/pastel)
         Responde "¿dónde están mis piezas físicas?"
         La dona es CSS puro: conic-gradient generado por PHP.
         No requiere JavaScript ni librería externa. -->
    <div class="panel grafico">
      <h2 class="grafico-titulo">Unidades en stock por categoría</h2>
      <div class="pastel-bloque">
        <!-- El círculo de la dona: su fondo es el gradiente cónico -->
        <div class="pastel" style="background: conic-gradient(<?= $gradientePastel ?>);"></div>
        <!-- Leyenda lateral con color, nombre, porcentaje y unidades -->
        <ul class="pastel-leyenda">
          <?php foreach ($leyendaPastel as $it): ?>
            <li>
              <span class="punto" style="background: <?= $it['color'] ?>;"></span>
              <span class="pastel-cat"><?= e($it['categoria']) ?></span>
              <span class="pastel-pct">
                <?= number_format($it['pct'], 1) ?>%
                (<?= number_format($it['unidades']) ?>)
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <?php endif; ?>

  </div><!-- /graficos-fila -->
  <?php endif; ?>

  <!-- ============================================================
       FORMULARIO DE FILTROS
       Envía GET a /productos con parámetros buscar y categoria.
       En impresión este formulario se oculta (ver @media print).
       ============================================================ -->
  <form method="get" action="<?= url('productos') ?>" class="filtros no-print">
    <!-- Campo de texto: busca por nombre de producto -->
    <input
      type="text"
      name="buscar"
      class="form-control"
      placeholder="Buscar por nombre..."
      value="<?= e($buscar) ?>"
    >
    <!-- Selector de categoría -->
    <select name="categoria" class="form-select">
      <option value="">Todas las categorías</option>
      <?php foreach ($categorias as $c): ?>
        <option value="<?= e($c) ?>" <?= ($categoria === $c ? 'selected' : '') ?>>
          <?= e($c) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Buscar</button>
    <?php if ($hayFiltro): ?>
      <!-- Solo aparece si hay filtros activos; limpia la búsqueda -->
      <a href="<?= url('productos') ?>" class="btn btn-outline-secondary">Limpiar</a>
    <?php endif; ?>
  </form>

  <?php if ($hayFiltro): ?>
    <!-- Conteo de resultados filtrados vs. total -->
    <p class="resultado-filtro">
      Mostrando <strong><?= count($productos) ?></strong>
      de <?= $totalSinFiltro ?> productos.
    </p>
  <?php endif; ?>

  <!-- ============================================================
       TABLA DE PRODUCTOS
       ============================================================ -->
  <?php if (count($productos) === 0): ?>
    <!-- Mensaje vacío: diferencia si no hay productos o no hubo coincidencias -->
    <div class="alert alert-info">
      <?= $hayFiltro
          ? 'Ningún producto coincide con la búsqueda.'
          : 'Todavía no hay productos. Agrega el primero con "+ Nuevo producto".' ?>
    </div>
  <?php else: ?>
  <div class="panel">
    <table class="inv">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th class="num">Precio</th>
          <th class="num">Stock</th>
          <th>Categoría</th>
          <!-- La columna de acciones se oculta en impresión (no-print en JS o CSS) -->
          <th class="num no-print">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($productos as $p):
            // Determinar el estado visual del stock
            $s = (int) $p['stock'];
            if ($s <= 0) {
                // Sin unidades: indicador rojo
                $clase = 'bad';
                $texto = 'Sin stock';
            } elseif ($s < 10) {
                // Stock bajo (menos de 10): indicador amarillo
                $clase = 'warn';
                $texto = $s . ' u';
            } else {
                // Stock normal: indicador verde
                $clase = 'ok';
                $texto = $s . ' u';
            }
        ?>
        <tr>
          <td class="id-cell">#<?= (int) $p['id'] ?></td>
          <td>
            <?= e($p['nombre']) ?>
            <?php if (!empty($p['fecha_creacion'])): ?>
              <!-- Fecha de alta del producto, formateada para el usuario -->
              <div class="fecha-alta">
                Agregado el <?= date('d/m/Y', strtotime($p['fecha_creacion'])) ?>
              </div>
            <?php endif; ?>
          </td>
          <td class="text-muted"><?= e($p['descripcion']) ?></td>
          <td class="num">$<?= number_format((float) $p['precio'], 2) ?></td>
          <td class="num">
            <!-- Píldora de color según estado del stock -->
            <span class="pill <?= $clase ?>"><?= $texto ?></span>
          </td>
          <td><?= e($p['categoria']) ?></td>
          <!-- Acciones: ocultas en impresión con la clase no-print -->
          <td class="no-print">
            <div class="actions">
              <a href="<?= url('productos/' . (int) $p['id'] . '/editar') ?>"
                 class="btn btn-sm btn-warning">Editar</a>
              <a href="<?= url('productos/' . (int) $p['id'] . '/eliminar') ?>"
                 class="btn btn-sm btn-danger">Eliminar</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

</main>

<?php require dirname(__DIR__, 2) . '/Views/layouts/footer.php'; ?>
