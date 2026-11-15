<?php
// Vista del listado + tablero. Solo arma HTML con lo que le paso el
// controlador. No consulta la base. Todo texto sale por e() (regla 3).
$titulo = 'Inventario de productos';
$navbar = true;
$menuActivo = 'productos';   // resalta Productos en el menu
require __DIR__ . '/../layouts/header.php';

// Para el ancho de las barras: la categoria con mas valor define el 100%.
$valorMayor = 0.0;
foreach ($porCategoria as $fila) {
    if ((float) $fila['valor'] > $valorMayor) $valorMayor = (float) $fila['valor'];
}

// ---- Datos del pastel: unidades en stock por categoria ----
// Sumamos TODAS las unidades: ese total es el 100% del pastel.
$totalUnidades = 0;
foreach ($stockPorCategoria as $fila) {
    $totalUnidades += (int) $fila['unidades'];
}
// Paleta fija de colores. Cada tajada toma uno por orden; si hay mas
// categorias que colores, la lista se repite con el modulo (%).
$coloresPastel = ['#2f5fe0', '#157f52', '#9a6212', '#b23b3b', '#6250d6', '#1b9e8f', '#c2569b', '#5a6b86'];
// En una sola pasada armamos dos cosas: las paradas del conic-gradient
// (cada trozo "color inicio% fin%") y la leyenda de al lado.
$paradasPastel = [];
$leyendaPastel = [];
$acumulado = 0.0;
$i = 0;
foreach ($stockPorCategoria as $fila) {
    $unidades = (int) $fila['unidades'];
    $pct   = $totalUnidades > 0 ? ($unidades / $totalUnidades) * 100 : 0;
    $color = $coloresPastel[$i % count($coloresPastel)];
    $inicio = $acumulado;
    $fin    = $acumulado + $pct;
    $paradasPastel[] = $color . ' ' . number_format($inicio, 2, '.', '') . '% ' . number_format($fin, 2, '.', '') . '%';
    $leyendaPastel[] = ['categoria' => $fila['categoria'], 'color' => $color, 'pct' => $pct, 'unidades' => $unidades];
    $acumulado = $fin;
    $i++;
}
$gradientePastel = implode(', ', $paradasPastel);
?>
<main class="shell">
  <div class="page-head">
    <h1>Productos</h1>
    <a href="<?= url('productos/crear') ?>" class="btn btn-primary">+ Nuevo producto</a>
  </div>

  <?php if ($hayFiltro): ?>
    <p class="resumen-filtrado">Resumen de los productos que coinciden, no del inventario completo.</p>
  <?php endif; ?>

  <div class="stats">
    <div class="stat valor"><div class="n">$<?= number_format((float) $m['valor'], 2) ?></div><div class="l">Valor del inventario</div></div>
    <div class="stat">      <div class="n"><?= (int) $m['productos'] ?></div>                   <div class="l">Productos</div></div>
    <div class="stat ok">   <div class="n"><?= number_format((int) $m['unidades']) ?></div>     <div class="l">Unidades en stock</div></div>
    <div class="stat warn"> <div class="n"><?= (int) $m['bajos'] ?></div>                       <div class="l">Con stock bajo (&lt;10)</div></div>
  </div>

  <?php if ($porCategoria || ($stockPorCategoria && $totalUnidades > 0)): ?>
  <!-- Los dos graficos del tablero, uno al lado del otro (ver .graficos-fila
       en estilo.css). En pantallas chicas se apilan solos. -->
  <div class="graficos-fila">

    <?php if ($porCategoria): ?>
    <!-- Grafico 1: VALOR por categoria (barras). Responde "donde esta mi dinero". -->
    <div class="panel grafico">
      <h2 class="grafico-titulo">Valor del inventario por categoria</h2>
      <?php foreach ($porCategoria as $fila):
          $ancho = $valorMayor > 0 ? ((float) $fila['valor'] / $valorMayor) * 100 : 0;
      ?>
        <div class="barra-fila">
          <div class="barra-etiqueta"><?= e($fila['categoria']) ?></div>
          <div class="barra-pista">
            <!-- El ancho va dentro de un atributo style: tambien se fuerza a
                 numero antes de imprimirlo (regla 3 aplicada a un atributo). -->
            <div class="barra" style="width: <?= number_format($ancho, 2, '.', '') ?>%"></div>
          </div>
          <div class="barra-valor">$<?= number_format((float) $fila['valor'], 2) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($stockPorCategoria && $totalUnidades > 0): ?>
    <!-- Grafico 2: UNIDADES por categoria (pastel/dona). Responde "donde estan
         mis piezas". La dona es puro CSS: el fondo es un conic-gradient que
         arma PHP con $gradientePastel; NO hay JavaScript ni libreria. -->
    <div class="panel grafico">
      <h2 class="grafico-titulo">Unidades en stock por categoria</h2>
      <div class="pastel-bloque">
        <div class="pastel" style="background: conic-gradient(<?= $gradientePastel ?>);"></div>
        <ul class="pastel-leyenda">
          <?php foreach ($leyendaPastel as $it): ?>
            <li>
              <span class="punto" style="background: <?= $it['color'] ?>;"></span>
              <span class="pastel-cat"><?= e($it['categoria']) ?></span>
              <span class="pastel-pct"><?= number_format($it['pct'], 1) ?>% (<?= number_format($it['unidades']) ?>)</span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <?php endif; ?>

  </div>
  <?php endif; ?>

  <!-- ====== LISTADO ====== -->
  <form method="get" action="<?= url('productos') ?>" class="filtros">
    <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre..." value="<?= e($buscar) ?>">
    <select name="categoria" class="form-select">
      <option value="">Todas las categorias</option>
      <?php foreach ($categorias as $c): ?>
        <option value="<?= e($c) ?>" <?= ($categoria === $c ? 'selected' : '') ?>><?= e($c) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Buscar</button>
    <?php if ($hayFiltro): ?>
      <a href="<?= url('productos') ?>" class="btn btn-outline-secondary">Limpiar</a>
    <?php endif; ?>
  </form>

  <?php if ($hayFiltro): ?>
    <p class="resultado-filtro">Mostrando <strong><?= count($productos) ?></strong> de <?= $totalSinFiltro ?> productos.</p>
  <?php endif; ?>

  <?php if (count($productos) === 0): ?>
    <div class="alert alert-info">
      <?= $hayFiltro ? 'Ningun producto coincide con la busqueda.' : 'Todavia no hay productos. Agrega el primero con "+ Nuevo producto".' ?>
    </div>
  <?php else: ?>
  <div class="panel">
    <table class="inv">
      <thead>
        <tr><th>ID</th><th>Nombre</th><th>Descripcion</th><th class="num">Precio</th><th class="num">Stock</th><th>Categoria</th><th class="num">Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($productos as $p):
            $s = (int) $p['stock'];
            if     ($s <= 0) { $clase = 'bad';  $texto = 'Sin stock'; }
            elseif ($s < 10) { $clase = 'warn'; $texto = $s . ' u'; }
            else             { $clase = 'ok';   $texto = $s . ' u'; }
        ?>
        <tr>
          <td class="id-cell">#<?= (int) $p['id'] ?></td>
          <td>
            <?= e($p['nombre']) ?>
            <?php if (!empty($p['fecha_creacion'])): ?>
              <div class="fecha-alta">Agregado el <?= date('d/m/Y', strtotime($p['fecha_creacion'])) ?></div>
            <?php endif; ?>
          </td>
          <td class="text-muted"><?= e($p['descripcion']) ?></td>
          <td class="num">$<?= number_format((float) $p['precio'], 2) ?></td>
          <td class="num"><span class="pill <?= $clase ?>"><?= $texto ?></span></td>
          <td><?= e($p['categoria']) ?></td>
          <td>
            <div class="actions">
              <a href="<?= url('productos/' . (int) $p['id'] . '/editar') ?>" class="btn btn-sm btn-warning">Editar</a>
              <a href="<?= url('productos/' . (int) $p['id'] . '/eliminar') ?>" class="btn btn-sm btn-danger">Eliminar</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
