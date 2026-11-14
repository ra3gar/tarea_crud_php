<?php
// index.php — Tablero y listado de productos. Pagina privada.
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

// =====================================================================
//  BLOQUE 1 — EL FILTRO
//  Se arma UNA sola vez, aca arriba, porque lo usan TODAS las consultas
//  de abajo: las del tablero y la del listado. Si cada una lo rehiciera,
//  seria el mismo codigo repetido tres veces con el mismo riesgo.
//
//  SEGURIDAD (regla 1 — inyeccion SQL). Lo que se arma con PHP es la
//  FORMA de la consulta: que condiciones entran. Y eso no lo decide el
//  usuario, lo decide si los campos vinieron vacios o no. Lo que el
//  usuario escribio NUNCA toca el texto del SQL: viaja aparte, en
//  $parametros, y MySQL lo trata como dato y no como instruccion.
//  Hasta los comodines % del LIKE se pegan al VALOR, no a la consulta.
// =====================================================================
$buscar    = trim($_GET['buscar']    ?? '');
$categoria = trim($_GET['categoria'] ?? '');

$condiciones = [];
$parametros  = [];
if ($buscar !== '') {
    $condiciones[] = "nombre LIKE :buscar";
    // Ojo con esto, que es sutil y distinto de la inyeccion SQL. Dentro de
    // un LIKE, el % y el _ son COMODINES: % significa "cualquier cosa" y _
    // "un caracter cualquiera". Si alguien busca "50% off", ese % se tomaria
    // como comodin y devolveria de mas. No es un agujero de seguridad —el
    // valor sigue viajando como parametro— pero da resultados equivocados.
    // Por eso se los neutraliza con una barra invertida antes de buscarlos.
    $buscar_literal = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $buscar);
    $parametros[':buscar'] = '%' . $buscar_literal . '%';
}
if ($categoria !== '') {
    $condiciones[] = "categoria = :categoria";
    $parametros[':categoria'] = $categoria;
}
$filtro     = $condiciones ? (" WHERE " . implode(" AND ", $condiciones)) : "";
$hay_filtro = ($buscar !== '' || $categoria !== '');

// =====================================================================
//  BLOQUE 2 — EL TABLERO
//  Las cuatro metricas salen de UNA sola consulta, no de recorrer los
//  productos en PHP: la base sabe sumar mejor que nosotros, y asi el
//  numero no depende de cuantas filas se hayan traido a la pagina.
//
//  Lleva el mismo $filtro que el listado, asi que el tablero siempre
//  describe lo que se esta viendo abajo. Si dijera el total del
//  inventario encima de una tabla filtrada, estaria mintiendo.
//
//  COALESCE(..., 0) esta a proposito: cuando el filtro no encuentra
//  nada, SUM() devuelve NULL y la tarjeta quedaria vacia en vez de 0.
//
//  CASE WHEN ... THEN 1 ELSE 0 END cuenta los que tienen poco stock.
//  Se podria escribir SUM(stock < 10), pero eso obliga al lector a
//  saber que MySQL trata los booleanos como 1 y 0. Asi se lee solo.
// =====================================================================
$sql_metricas = "SELECT COUNT(*)                                                 AS productos,
                        COALESCE(SUM(stock), 0)                                  AS unidades,
                        COALESCE(SUM(precio * stock), 0)                         AS valor,
                        COALESCE(SUM(CASE WHEN stock < 10 THEN 1 ELSE 0 END), 0) AS bajos
                 FROM productos" . $filtro;
$stmt_metricas = $pdo->prepare($sql_metricas);
$stmt_metricas->execute($parametros);
$m = $stmt_metricas->fetch();

$total_prod     = (int)   $m['productos'];
$total_unidades = (int)   $m['unidades'];
$valor_total    = (float) $m['valor'];
$bajo           = (int)   $m['bajos'];

// --- El grafico: valor del inventario por categoria ---------------
//  COALESCE(NULLIF(categoria, ''), 'Sin categoria') junta dos casos en
//  una sola etiqueta. NULLIF devuelve NULL si la categoria es cadena
//  vacia, y tambien es NULL si ya venia nula (la columna lo permite);
//  COALESCE agarra los dos y les pone el mismo nombre.
//  Sin eso, los productos sin categoria desapareceran del grafico pero
//  seguirian contando en "Valor del inventario", y los numeros de la
//  pantalla no cerrarian entre si.
$sql_grafico = "SELECT COALESCE(NULLIF(categoria, ''), 'Sin categoria') AS categoria,
                       SUM(precio * stock)                              AS valor
                FROM productos" . $filtro . "
                GROUP BY COALESCE(NULLIF(categoria, ''), 'Sin categoria')
                ORDER BY valor DESC";
$stmt_grafico = $pdo->prepare($sql_grafico);
$stmt_grafico->execute($parametros);
$por_categoria = $stmt_grafico->fetchAll();

// La categoria con mas valor es la que define el 100% del ancho.
$valor_mayor = 0.0;
foreach ($por_categoria as $fila) {
    if ((float)$fila['valor'] > $valor_mayor) $valor_mayor = (float)$fila['valor'];
}

// =====================================================================
//  BLOQUE 3 — EL LISTADO
//  La misma consulta de siempre, pero con el filtro pegado. Fijate que
//  $filtro entra en el texto del SQL y $parametros va por separado al
//  execute(): esa separacion es toda la defensa.
// =====================================================================
$sql = "SELECT id, nombre, descripcion, precio, stock, categoria, fecha_creacion
        FROM productos" . $filtro . " ORDER BY id";
$stmt_listado = $pdo->prepare($sql);
$stmt_listado->execute($parametros);
$productos = $stmt_listado->fetchAll();

// Cuantos hay en total, sin filtrar. Sirve para el "mostrando X de Y".
$total_sin_filtro = (int)$pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();

// =====================================================================
//  BLOQUE 4 — EL DESPLEGABLE DE CATEGORIAS
//  A proposito NO lleva el filtro: tiene que listar siempre todas. Si
//  se filtrara, al elegir una categoria desapareceran las demas del
//  desplegable y no habria forma de volver atras.
// =====================================================================
$categorias = $pdo->query(
    "SELECT DISTINCT categoria FROM productos WHERE categoria <> '' ORDER BY categoria"
)->fetchAll(PDO::FETCH_COLUMN);

$titulo = "Inventario de productos";
$navbar = true;
require __DIR__ . '/includes/header.php';
?>
<main class="shell">
  <div class="page-head">
    <h1>Productos</h1>
    <a href="crear.php" class="btn btn-primary">+ Nuevo producto</a>
  </div>

  <?php if (!empty($_SESSION['mensaje'])):
      $msj = $_SESSION['mensaje'];
      // Se borra APENAS se lee. Ese unset es lo que hace que el aviso
      // salga una sola vez: si quedara guardado, reapareceria en cada
      // recarga de la pagina.
      unset($_SESSION['mensaje']);
  ?>
    <div class="alert alert-<?= ($msj['tipo'] === 'error' ? 'danger' : 'success') ?>"><?= htmlspecialchars($msj['texto']) ?></div>
  <?php endif; ?>

  <?php if ($hay_filtro): ?>
    <p class="resumen-filtrado">Resumen de los productos que coinciden, no del inventario completo.</p>
  <?php endif; ?>

  <div class="stats">
    <div class="stat valor"><div class="n">$<?= number_format($valor_total, 2) ?></div><div class="l">Valor del inventario</div></div>
    <div class="stat">      <div class="n"><?= $total_prod ?></div>                   <div class="l">Productos</div></div>
    <div class="stat ok">   <div class="n"><?= number_format($total_unidades) ?></div><div class="l">Unidades en stock</div></div>
    <div class="stat warn"> <div class="n"><?= $bajo ?></div>                        <div class="l">Con stock bajo (&lt;10)</div></div>
  </div>

  <?php if ($por_categoria): ?>
  <div class="panel grafico">
    <h2 class="grafico-titulo">Valor del inventario por categoria</h2>
    <?php foreach ($por_categoria as $fila):
        // El ancho es una division, asi que hay que cuidar el caso en que
        // el valor mayor sea 0: dividir por cero tumbaria la pagina.
        $ancho = $valor_mayor > 0 ? ((float)$fila['valor'] / $valor_mayor) * 100 : 0;
    ?>
      <div class="barra-fila">
        <div class="barra-etiqueta"><?= htmlspecialchars($fila['categoria']) ?></div>
        <div class="barra-pista">
          <!-- SEGURIDAD (regla 3 — XSS): esto se imprime DENTRO de un
               atributo style. Un atributo tambien es un lugar donde un
               valor sin controlar seria una inyeccion, igual que dentro
               de una celda. Aca el numero sale de un SUM() de la base y
               es seguro, pero igual se fuerza a numero antes de
               imprimirlo: protege el habito, no la suerte. -->
          <div class="barra" style="width: <?= number_format($ancho, 2, '.', '') ?>%"></div>
        </div>
        <div class="barra-valor">$<?= number_format((float)$fila['valor'], 2) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- ============================================================
       Aca termina el TABLERO y empieza el LISTADO.
       ============================================================ -->

  <!-- La busqueda se envia por GET para que el resultado se pueda
       recargar o compartir: el filtro queda escrito en la direccion.
       SEGURIDAD (regla 3 — XSS): el value del campo repinta en pantalla
       lo que la persona escribio. Es el caso de manual, por eso pasa por
       htmlspecialchars() igual que cualquier otro dato. -->
  <form method="get" class="filtros">
    <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre..."
           value="<?= htmlspecialchars($buscar) ?>">
    <select name="categoria" class="form-select">
      <option value="">Todas las categorias</option>
      <?php foreach ($categorias as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>" <?= ($categoria === $c ? 'selected' : '') ?>><?= htmlspecialchars($c) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Buscar</button>
    <?php if ($hay_filtro): ?>
      <a href="index.php" class="btn btn-outline-secondary">Limpiar</a>
    <?php endif; ?>
  </form>

  <?php if ($hay_filtro): ?>
    <p class="resultado-filtro">Mostrando <strong><?= $total_prod ?></strong> de <?= $total_sin_filtro ?> productos.</p>
  <?php endif; ?>

  <?php if ($total_prod === 0): ?>
    <?php if ($hay_filtro): ?>
      <div class="alert alert-info">Ningun producto coincide con la busqueda. Proba con otro texto o <a href="index.php">limpia el filtro</a>.</div>
    <?php else: ?>
      <div class="alert alert-info">Todavia no hay productos. Agrega el primero con "+ Nuevo producto".</div>
    <?php endif; ?>
  <?php else: ?>
  <div class="panel">
    <table class="inv">
      <thead>
        <tr>
          <th>ID</th><th>Nombre</th><th>Descripcion</th>
          <th class="num">Precio</th><th class="num">Stock</th><th>Categoria</th><th class="num">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($productos as $p):
            $s = (int)$p['stock'];
            if     ($s <= 0) { $clase = 'bad';  $texto = 'Sin stock'; }
            elseif ($s < 10) { $clase = 'warn'; $texto = $s . ' u'; }
            else             { $clase = 'ok';   $texto = $s . ' u'; }
        ?>
        <tr>
          <td class="id-cell">#<?= $p['id'] ?></td>
          <td>
            <?= htmlspecialchars($p['nombre']) ?>
            <?php if (!empty($p['fecha_creacion'])): ?>
              <div class="fecha-alta">Agregado el <?= date('d/m/Y', strtotime($p['fecha_creacion'])) ?></div>
            <?php endif; ?>
          </td>
          <td class="text-muted"><?= htmlspecialchars((string)$p['descripcion']) ?></td>
          <td class="num">$<?= number_format($p['precio'], 2) ?></td>
          <td class="num"><span class="pill <?= $clase ?>"><?= $texto ?></span></td>
          <!-- El (string) no sobra: descripcion y categoria admiten NULL en
               la base, y pasarle null a htmlspecialchars esta deprecado en
               PHP 8. Sin el cast, una fila sin categoria imprimiria un aviso
               de deprecacion en medio de la tabla. -->
          <td><?= htmlspecialchars((string)$p['categoria']) ?></td>
          <td>
            <div class="actions">
              <a href="editar.php?id=<?= $p['id'] ?>"  class="btn btn-sm btn-warning">Editar</a>
              <a href="eliminar.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger">Eliminar</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
