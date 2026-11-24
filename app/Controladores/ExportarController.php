<?php
// =====================================================================
//  app/Controladores/ExportarController.php — Punto de entrada de exportación
//
//  Ubicación en la arquitectura MVC clásica:
//    app/Controladores/ExportarController.php  <- este archivo
//    app/Servicios/PdfExporter.php             <- servicio que genera el PDF
//    app/Servicios/ExcelExporter.php           <- servicio que genera el .xlsx
//    app/Vistas/exportar/                      <- plantillas HTML para los PDFs
//
//  Qué hace:
//    Recibe las peticiones de exportación (PDF o Excel), consulta los
//    datos al modelo del módulo correspondiente (Producto, Categoria, Usuario),
//    y delega la generación del archivo al servicio adecuado.
//
//  Rutas que maneja (definidas en routes/web.php):
//    GET /exportar/productos/pdf
//    GET /exportar/productos/excel
//    GET /exportar/categorias/pdf
//    GET /exportar/categorias/excel
//    GET /exportar/usuarios/pdf     (solo admin)
//    GET /exportar/usuarios/excel   (solo admin)
//
//  Por qué el controlador es tan pequeño:
//    El trabajo de generar el archivo lo hacen PdfExporter y ExcelExporter.
//    El controlador solo decide QUÉ datos pedir y QUÉ nombre darle al archivo.
//    Esto sigue el principio de responsabilidad única: cada clase hace una cosa.
//
//  SEGURIDAD:
//    Las rutas de /usuarios llevan permiso 'admin' en routes/web.php.
//    Este controlador no necesita comprobarlo de nuevo: el Router lo garantiza.
// =====================================================================

namespace App\Controladores;

// Modelos de los otros módulos — se consultan para obtener los datos
use App\Modelos\Producto;
use App\Modelos\Categoria;
use App\Modelos\Usuario;
use App\Servicios\PdfExporter;
use App\Servicios\ExcelExporter;

/**
 * Controlador de exportación.
 *
 * Cada método corresponde a un tipo de exportación + formato.
 * El patrón es uniforme: obtener datos del modelo → llamar al exportador.
 */
class ExportarController
{
    // =========================================================================
    //  EXPORTACIONES DE PRODUCTOS
    // =========================================================================

    /**
     * GET /exportar/productos/pdf
     *
     * Genera un PDF con todos los productos del inventario y lo envía como descarga.
     * El PDF incluye el listado completo; no aplica filtros de búsqueda porque
     * un informe exportado debe reflejar la situación completa del inventario.
     *
     * @return void  El método termina llamando a PdfExporter::generar() que hace exit().
     */
    public function productosPdf(): void
    {
        // Obtener todos los productos sin filtro ('' = sin búsqueda, '' = sin categoría)
        $productos = Producto::todos();

        // Nombre del archivo con la fecha actual para identificar cuándo se exportó
        $nombreArchivo = 'inventario-productos-' . date('Y-m-d') . '.pdf';

        // Delegar al servicio PDF con los datos y el nombre de la plantilla HTML
        PdfExporter::generar('pdf_productos', ['productos' => $productos], $nombreArchivo);
    }

    /**
     * GET /exportar/productos/excel
     *
     * Genera un archivo .xlsx con todos los productos y lo envía como descarga.
     *
     * @return void  El método termina llamando a ExcelExporter::generar() que hace exit().
     */
    public function productosExcel(): void
    {
        $productos     = Producto::todos();
        $nombreArchivo = 'inventario-productos-' . date('Y-m-d') . '.xlsx';

        // El primer argumento 'productos' le dice al ExcelExporter qué columnas usar
        ExcelExporter::generar('productos', ['productos' => $productos], $nombreArchivo);
    }

    // =========================================================================
    //  EXPORTACIONES DE CATEGORÍAS
    // =========================================================================

    /**
     * GET /exportar/categorias/pdf
     *
     * Genera un PDF con todas las categorías y cuántos productos tiene cada una.
     *
     * @return void
     */
    public function categoriasPdf(): void
    {
        // Categorías con conteo de productos asociados
        $categorias = self::categoriasConConteo();

        $nombreArchivo = 'categorias-' . date('Y-m-d') . '.pdf';
        PdfExporter::generar('pdf_categorias', ['categorias' => $categorias], $nombreArchivo);
    }

    /**
     * GET /exportar/categorias/excel
     *
     * Genera un .xlsx con todas las categorías y su conteo de productos.
     *
     * @return void
     */
    public function categoriasExcel(): void
    {
        $categorias    = self::categoriasConConteo();
        $nombreArchivo = 'categorias-' . date('Y-m-d') . '.xlsx';

        ExcelExporter::generar('categorias', ['categorias' => $categorias], $nombreArchivo);
    }

    // =========================================================================
    //  EXPORTACIONES DE USUARIOS  (solo admin — permiso declarado en routes/web.php)
    // =========================================================================

    /**
     * GET /exportar/usuarios/pdf
     *
     * Genera un PDF con el listado de usuarios del sistema.
     * No incluye contraseñas (ni el hash) por seguridad.
     *
     * @return void
     */
    public function usuariosPdf(): void
    {
        $usuarios      = Usuario::todos();
        $nombreArchivo = 'usuarios-' . date('Y-m-d') . '.pdf';

        PdfExporter::generar('pdf_usuarios', ['usuarios' => $usuarios], $nombreArchivo);
    }

    /**
     * GET /exportar/usuarios/excel
     *
     * Genera un .xlsx con el listado de usuarios del sistema.
     *
     * @return void
     */
    public function usuariosExcel(): void
    {
        $usuarios      = Usuario::todos();
        $nombreArchivo = 'usuarios-' . date('Y-m-d') . '.xlsx';

        ExcelExporter::generar('usuarios', ['usuarios' => $usuarios], $nombreArchivo);
    }

    // =========================================================================
    //  MÉTODOS AUXILIARES PRIVADOS
    // =========================================================================

    /**
     * Devuelve todas las categorías con un campo adicional 'total_productos'
     * que indica cuántos productos tiene asignados cada una.
     *
     * Se usa en ambos formatos (PDF y Excel) de categorías para no repetir
     * la consulta SQL dos veces.
     *
     * Por qué no está en el modelo Categoria:
     *   Esta consulta específica (categorias + conteo de productos) pertenece
     *   al caso de uso de exportación, no al modelo general. Ponerla aquí
     *   mantiene al modelo simple y reutilizable.
     *
     * @return array<int,array{id:int,nombre:string,total_productos:int}>
     */
    private static function categoriasConConteo(): array
    {
        // JOIN entre categorias y productos para contar cuántos productos
        // tienen asignada cada categoría. LEFT JOIN porque queremos ver
        // también las categorías sin productos (con total 0).
        $stmt = db()->query(
            "SELECT c.id,
                    c.nombre,
                    COUNT(p.id) AS total_productos
             FROM categorias c
             LEFT JOIN productos p ON p.categoria = c.nombre
             GROUP BY c.id, c.nombre
             ORDER BY c.nombre"
        );
        return $stmt->fetchAll();
    }
}
