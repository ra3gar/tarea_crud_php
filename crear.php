<?php
// =====================================================================
//  crear.php — AGREGAR un producto al inventario (la "C" de CRUD).
//
//  QUE ES
//    Pagina privada con el formulario de alta de productos.
//
//  QUE RECIBE
//    - Por POST, al enviarse el formulario: nombre, descripcion, precio,
//      stock y categoria.
//    - De auth.php: la sesion ya iniciada. Si no hay sesion, la ejecucion
//      nunca llega hasta aca.
//    - De config.php: $pdo, la conexion a la base ya abierta.
//
//  QUE HACE
//    1. Exige sesion iniciada y abre la conexion a la base.
//    2. Si la visita llega por GET, muestra el formulario vacio.
//    3. Si llega por POST, lee los datos, los valida y, si estan bien,
//       inserta el producto y manda al listado.
//    4. Si la validacion falla, vuelve a mostrar el formulario con los
//       errores arriba y los datos que la persona ya habia escrito.
//
//  QUE ENTREGA
//    - Si guarda: una redireccion a index.php, sin imprimir HTML.
//    - Si hay errores, o es la primera visita: el HTML del formulario.
//
//  DONDE ENCAJA
//    Se llega desde el boton "+ Nuevo producto" de index.php.
//    Se sale a index.php, tanto al guardar como al cancelar.
//
//  VER TAMBIEN
//    El patron de "una pagina, dos modos", que comparte con editar.php,
//    login.php, registro.php y recuperar.php:
//      docs/guia-lectura.html#patrones-get-post
//    La validacion que comparte con editar.php:
//      docs/guia-lectura.html#patron-validacion-producto
//    Como se habla con la base sin riesgo de inyeccion SQL:
//      docs/guia-lectura.html#glosario-pdo
//    El recorrido completo de este alta, de punta a punta:
//      docs/guia-lectura.html#caso-completo
// =====================================================================
// Este es el orden obligatorio de toda pagina privada: primero el
// guardia, despues la conexion. auth.php corta la ejecucion y redirige
// al login si no hay sesion, asi que nada de lo que sigue se ejecuta
// para un visitante sin credenciales.
// Ver docs/guia-lectura.html#glosario-includes
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

// Las categorias que ya existen, para ofrecerlas en el desplegable. Es la
// misma consulta que usa index.php: una lista sin repetidos. Asi se deja
// de ensuciar los datos con "Perifericos", "perifericos" y "Perifericos "
// como si fueran tres categorias distintas.
$categorias = $pdo->query(
    "SELECT DISTINCT categoria FROM productos WHERE categoria <> '' ORDER BY categoria"
)->fetchAll(PDO::FETCH_COLUMN);

// Las cinco variables del formulario arrancan vacias, y $errores como
// lista vacia. Existen desde aca, antes de saber si la visita es GET o
// POST, porque el HTML del final las imprime en los campos en los dos
// casos: si no existieran, un GET daria aviso de variable indefinida.
// $categoria_nueva arranca vacia junto con las demas por una razon concreta:
// el HTML del final la imprime en su campo. Si no existiera, una categoria
// recien escrita se perderia en silencio cuando la validacion falla, y el
// producto terminaria guardandose sin categoria sin que nadie se entere.
$nombre = $descripcion = $precio = $stock = $categoria = $categoria_nueva = '';
$errores = [];

// De aca en adelante hay dos caminos posibles. Si el navegador pidio la
// pagina con GET (alguien hizo clic en "+ Nuevo producto"), todo este
// bloque se saltea y mas abajo se dibuja el formulario vacio. Si llego
// por POST, es que el formulario se envio y hay datos que procesar.
// Ver docs/guia-lectura.html#patrones-get-post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se leen los cinco campos. trim() quita los espacios sobrantes de
    // los extremos, y ?? '' deja cadena vacia si el campo no vino, para
    // que las comparaciones de abajo nunca reciban null.
    // Ver docs/guia-lectura.html#glosario-funciones-texto
    $nombre      = trim($_POST['nombre']      ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio      = trim($_POST['precio']      ?? '');
    $stock       = trim($_POST['stock']       ?? '');
    // Hay dos campos para una sola columna. Si la persona escribio una
    // categoria nueva, esa gana; si no, vale la que eligio del desplegable.
    // La regla se decide ACA, en el servidor. Sin JavaScript no hay campos
    // que aparezcan y desaparezcan, asi que lo que decide es el codigo.
    $categoria_nueva = trim($_POST['categoria_nueva'] ?? '');
    $categoria   = $categoria_nueva !== '' ? $categoria_nueva : trim($_POST['categoria'] ?? '');

    // Validacion: tres reglas, las mismas que usa editar.php. El nombre
    // no puede quedar vacio, y precio y stock tienen que ser numeros de
    // cero en adelante. Cero es valido a proposito: un producto agotado
    // tiene stock 0, y un articulo promocional puede tener precio 0.
    // Cada error se acumula en la lista en vez de cortar en el primero,
    // para poder mostrarlos todos juntos y que la persona corrija de una.
    // Ver docs/guia-lectura.html#patron-validacion-producto
    if ($nombre === '')                       $errores[] = "El nombre es obligatorio.";
    if (!is_numeric($precio) || $precio < 0)  $errores[] = "El precio debe ser un numero valido (>= 0).";
    if (!is_numeric($stock)  || $stock < 0)   $errores[] = "El stock debe ser un numero valido (>= 0).";

    // Solo si no quedo ni un error se escribe en la base.
    if (empty($errores)) {
        // Aca esta la invariante numero 1 del proyecto: los valores NUNCA
        // se pegan dentro del texto del SQL. Viajan como marcadores
        // (:nombre, :precio, ...) y el valor real va aparte, en el
        // execute(). Asi, un nombre de producto que contenga una comilla
        // o un fragmento de SQL se guarda como texto y no se ejecuta.
        // Ver docs/guia-lectura.html#glosario-pdo
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, categoria)
                VALUES (:nombre, :descripcion, :precio, :stock, :categoria)";
        $pdo->prepare($sql)->execute([
            ':nombre'=>$nombre, ':descripcion'=>$descripcion,
            ':precio'=>$precio, ':stock'=>$stock, ':categoria'=>$categoria,
        ]);
        // Guardado. Se redirige al listado en lugar de imprimir HTML, y
        // es a proposito: si esta pagina respondiera con HTML aca, al
        // recargar el navegador reenviaria el POST y crearia el producto
        // dos veces. El exit corta la ejecucion para que no se dibuje
        // nada despues de la redireccion.
        // El aviso viaja en la sesion y no en la direccion. Si fuera
        // ?creado=1, volveria a aparecer cada vez que alguien recargue o
        // comparta el enlace. En la sesion se lee una vez y se borra.
        $_SESSION['mensaje'] = ['texto' => 'Producto creado.', 'tipo' => 'ok'];
        header("Location: index.php");
        exit;
    }
}

// Dos variables que header.php lee cuando se lo incluye: el texto del
// <title> y si hay que dibujar la barra superior. Se definen ANTES del
// require, porque el archivo incluido las usa en el momento en que entra.
// Ver docs/guia-lectura.html#glosario-includes
$titulo = "Nuevo producto";
$navbar = true;
require __DIR__ . '/includes/header.php';
?>
<!-- =================================================================
     De aca al final es la VISTA: el formulario que ve la persona.
     Si llegamos por POST y la validacion fallo, los campos se repintan
     con lo que ya habia escrito, para que no tenga que tipear de nuevo.
     ================================================================= -->
<main class="shell narrow">
  <div class="page-head"><h1>Nuevo producto</h1><a href="index.php" class="btn btn-outline-secondary">Volver</a></div>

  <!-- Los errores de validacion, si hubo. htmlspecialchars() escapa el
       texto antes de imprimirlo: es la defensa contra que alguien logre
       inyectar HTML o JavaScript en la pagina.
       Ver docs/guia-lectura.html#glosario-funciones-texto -->
  <?php if (!empty($errores)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <!-- El formulario se envia a esta misma pagina: no tiene action, y eso
       es lo que habilita el patron de dos modos del principio. Cada campo
       trae su value ya escapado, para repintar lo que se escribio cuando
       la validacion falla. -->
  <form method="post" class="card card-body">
    <div class="mb-3">
      <label class="form-label">Nombre *</label>
      <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($nombre) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Descripcion</label>
      <input type="text" name="descripcion" class="form-control" value="<?= htmlspecialchars($descripcion) ?>">
    </div>
    <div class="row">
      <div class="col mb-3">
        <label class="form-label">Precio *</label>
        <input type="number" step="0.01" min="0" name="precio" class="form-control" value="<?= htmlspecialchars($precio) ?>" required>
      </div>
      <div class="col mb-3">
        <label class="form-label">Stock *</label>
        <input type="number" min="0" name="stock" class="form-control" value="<?= htmlspecialchars($stock) ?>" required>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Categoria</label>
      <!-- Los dos controles van SIEMPRE visibles. La alternativa habitual
           —un campo que aparece al elegir "Otra"— necesita JavaScript, y
           este proyecto es PHP puro a proposito. Asi funciona en cualquier
           navegador y la regla de cual gana se lee en el servidor. -->
      <select name="categoria" class="form-select mb-2">
        <option value="">(Sin categoria)</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>" <?= ($categoria === $c ? 'selected' : '') ?>><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="categoria_nueva" class="form-control" placeholder="...o escribi una categoria nueva"
             value="<?= htmlspecialchars($categoria_nueva) ?>">
      <div class="form-text">Si escribis una categoria nueva, se usa esa y se ignora la elegida arriba.</div>
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Guardar producto</button>
      <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </form>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
