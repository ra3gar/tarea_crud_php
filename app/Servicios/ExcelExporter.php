<?php
// =====================================================================
//  app/Modules/Exportar/ExcelExporter.php — Generador de archivos Excel
//
//  Ubicación en la arquitectura por módulos:
//    app/Modules/Exportar/
//      ExcelExporter.php      <- este archivo (servicio Excel)
//      PdfExporter.php        <- servicio PDF
//      ExportarController.php <- controlador del módulo
//      Views/                 <- plantillas HTML (solo para PDF)
//
//  Qué hace:
//    Envuelve la biblioteca PhpSpreadsheet para construir un archivo .xlsx
//    en memoria y enviarlo al navegador como descarga, sin guardar nada
//    en disco.
//
//  Por qué PhpSpreadsheet:
//    - Sucesor oficial de PHPExcel (el que todos conocían).
//    - Licencia MIT: libre para uso académico y comercial.
//    - PHP 8.1+, activamente mantenida (v3.x).
//    - Genera .xlsx real (XML dentro de ZIP), no HTML disfrazado de Excel.
//
//  Cómo funciona el flujo:
//    1. ExportarController llama ExcelExporter::generar('productos', $datos, 'archivo.xlsx').
//    2. Este clase recibe el nombre del "tipo" para saber qué columnas
//       y datos usar (productos, categorias o usuarios).
//    3. Se crea el Spreadsheet en memoria, se rellenan filas y se le da
//       un estilo mínimo para que sea legible.
//    4. Se envían las cabeceras HTTP y el .xlsx llega al navegador.
//
//  SEGURIDAD:
//    El nombre del archivo .xlsx se construye aquí; no viene del usuario.
// =====================================================================

namespace App\Servicios;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Servicio de exportación a Excel (.xlsx).
 *
 * Clase sin estado (todos los métodos estáticos).
 * Cada tipo de exportación tiene su propio método interno de relleno
 * para mantener el código organizado.
 */
class ExcelExporter
{
    // -----------------------------------------------------------------------
    // COLOR DE ENCABEZADO: azul oscuro del tema Bootstrap del proyecto.
    // Se usa el mismo tono en todos los informes para coherencia visual.
    // -----------------------------------------------------------------------
    private const COLOR_ENCABEZADO = '2f5fe0'; // Sin el # inicial (formato ARGB sin canal alpha)
    private const COLOR_TEXTO_ENC  = 'FFFFFF'; // Blanco para contraste sobre el azul
    private const COLOR_FILA_ALT   = 'EEF2FF'; // Azul muy claro para filas alternadas

    /**
     * Genera un archivo .xlsx y lo envía al navegador como descarga.
     *
     * @param string               $tipo         Qué datos exportar: 'productos',
     *                                           'categorias' o 'usuarios'.
     * @param array<string, mixed> $datos         Datos del modelo (mismas claves
     *                                           que usa cada método de relleno).
     * @param string               $nombreArchivo Nombre del .xlsx para el usuario.
     * @return void  Termina con exit().
     */
    public static function generar(string $tipo, array $datos, string $nombreArchivo): void
    {
        // ---- 1. Crear el libro de trabajo ------------------------------------
        $spreadsheet = new Spreadsheet();

        // Propiedades del documento (se ven en "Propiedades del archivo" en Excel)
        $spreadsheet->getProperties()
            ->setCreator('Sistema de Inventario')
            ->setTitle('Exportación — ' . ucfirst($tipo))
            ->setDescription('Generado automáticamente por el sistema de inventario.');

        // La hoja activa por defecto se llama "Sheet" (en inglés).
        // La renombramos al tipo de datos que contiene.
        $hoja = $spreadsheet->getActiveSheet();
        $hoja->setTitle(ucfirst($tipo));

        // ---- 2. Rellenar filas según el tipo --------------------------------
        // Cada método recibe la hoja y los datos, y devuelve el número de
        // columnas usadas para poder autoajustar el ancho al final.
        switch ($tipo) {
            case 'productos':
                $totalColumnas = self::rellenarProductos($hoja, $datos['productos'] ?? []);
                break;
            case 'categorias':
                $totalColumnas = self::rellenarCategorias($hoja, $datos['categorias'] ?? []);
                break;
            case 'usuarios':
                $totalColumnas = self::rellenarUsuarios($hoja, $datos['usuarios'] ?? []);
                break;
            default:
                // Tipo desconocido: error de programación, no del usuario.
                http_response_code(500);
                echo "Error interno: tipo de exportación '$tipo' no reconocido.";
                exit();
        }

        // ---- 3. Autoajustar ancho de columnas --------------------------------
        // columnDimensionByColumn() usa índice base-1; iteramos de 1 al total.
        for ($col = 1; $col <= $totalColumnas; $col++) {
            $hoja->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        // ---- 4. Enviar al navegador ------------------------------------------
        // Limpiar salida anterior (espacios BOM, avisos PHP) que corromperían el ZIP.
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Cabeceras MIME para Excel 2007+ (.xlsx = OpenXML)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Cache-Control: max-age=0');

        // El Writer escribe directamente a php://output (stdout del navegador).
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit();
    }

    // =========================================================================
    //  MÉTODOS DE RELLENO — uno por tipo de exportación
    // =========================================================================

    /**
     * Rellena la hoja con los datos de productos.
     *
     * Columnas: ID | Nombre | Descripción | Precio | Stock | Categoría | Fecha
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja
     * @param array<int,array> $productos  Filas devueltas por Producto::todos().
     * @return int Número de columnas usadas.
     */
    private static function rellenarProductos($hoja, array $productos): int
    {
        // Encabezados — fila 1
        $encabezados = ['ID', 'Nombre', 'Descripción', 'Precio ($)', 'Stock', 'Categoría', 'Fecha de alta'];
        self::escribirEncabezados($hoja, $encabezados);

        // Datos — a partir de la fila 2
        $fila = 2;
        foreach ($productos as $p) {
            $hoja->setCellValue('A' . $fila, $p['id']);
            $hoja->setCellValue('B' . $fila, $p['nombre']);
            $hoja->setCellValue('C' . $fila, $p['descripcion'] ?? '');
            // Precio como número para que Excel pueda sumar/graficar
            $hoja->setCellValue('D' . $fila, (float) $p['precio']);
            $hoja->setCellValue('E' . $fila, (int) $p['stock']);
            $hoja->setCellValue('F' . $fila, $p['categoria'] ?? 'Sin categoría');
            $hoja->setCellValue('G' . $fila, $p['fecha_creacion'] ?? '');

            // Estilo de filas alternadas para facilitar la lectura
            if ($fila % 2 === 0) {
                self::colorearFila($hoja, $fila, count($encabezados));
            }
            $fila++;
        }

        // Formato de número para la columna de precio (dos decimales)
        if ($fila > 2) {
            $hoja->getStyle('D2:D' . ($fila - 1))
                 ->getNumberFormat()
                 ->setFormatCode('#,##0.00');
        }

        return count($encabezados);
    }

    /**
     * Rellena la hoja con los datos de categorías.
     *
     * Columnas: ID | Nombre | Productos asignados
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja
     * @param array<int,array> $categorias  Filas devueltas por Categoria::todas() + conteo.
     * @return int Número de columnas usadas.
     */
    private static function rellenarCategorias($hoja, array $categorias): int
    {
        $encabezados = ['ID', 'Nombre de categoría', 'Productos asignados'];
        self::escribirEncabezados($hoja, $encabezados);

        $fila = 2;
        foreach ($categorias as $c) {
            $hoja->setCellValue('A' . $fila, $c['id']);
            $hoja->setCellValue('B' . $fila, $c['nombre']);
            $hoja->setCellValue('C' . $fila, (int) ($c['total_productos'] ?? 0));

            if ($fila % 2 === 0) {
                self::colorearFila($hoja, $fila, count($encabezados));
            }
            $fila++;
        }

        return count($encabezados);
    }

    /**
     * Rellena la hoja con los datos de usuarios.
     *
     * Columnas: ID | Nombre | Apellido | Correo | Rol | Fecha de registro
     * No se exporta la contraseña (hash) por seguridad.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja
     * @param array<int,array> $usuarios  Filas devueltas por Usuario::todos().
     * @return int Número de columnas usadas.
     */
    private static function rellenarUsuarios($hoja, array $usuarios): int
    {
        $encabezados = ['ID', 'Nombre', 'Apellido', 'Correo electrónico', 'Rol', 'Fecha de registro'];
        self::escribirEncabezados($hoja, $encabezados);

        $fila = 2;
        foreach ($usuarios as $u) {
            $hoja->setCellValue('A' . $fila, $u['id']);
            $hoja->setCellValue('B' . $fila, $u['nombre']);
            $hoja->setCellValue('C' . $fila, $u['apellido']);
            $hoja->setCellValue('D' . $fila, $u['correo']);
            $hoja->setCellValue('E' . $fila, $u['rol']);
            $hoja->setCellValue('F' . $fila, $u['fecha_creacion'] ?? '');

            if ($fila % 2 === 0) {
                self::colorearFila($hoja, $fila, count($encabezados));
            }
            $fila++;
        }

        return count($encabezados);
    }

    // =========================================================================
    //  HELPERS DE ESTILO
    // =========================================================================

    /**
     * Escribe los encabezados en la fila 1 con fondo azul y texto blanco.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja
     * @param string[] $encabezados Textos de las columnas.
     * @return void
     */
    private static function escribirEncabezados($hoja, array $encabezados): void
    {
        $col = 'A';
        foreach ($encabezados as $texto) {
            $celda = $col . '1';
            $hoja->setCellValue($celda, $texto);

            // Fondo sólido azul
            $hoja->getStyle($celda)->getFill()
                 ->setFillType(Fill::FILL_SOLID)
                 ->getStartColor()->setRGB(self::COLOR_ENCABEZADO);

            // Texto blanco y negrita
            $hoja->getStyle($celda)->getFont()
                 ->setBold(true)
                 ->getColor()->setRGB(self::COLOR_TEXTO_ENC);

            // Alineación centrada
            $hoja->getStyle($celda)->getAlignment()
                 ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Borde inferior para separar encabezado de datos
            $hoja->getStyle($celda)->getBorders()->getBottom()
                 ->setBorderStyle(Border::BORDER_MEDIUM)
                 ->getColor()->setRGB('FFFFFF');

            $col = str_increment($col);
        }
    }

    /**
     * Aplica color de fondo azul claro a una fila completa.
     *
     * Se usa en filas pares para el efecto "cebra" (alternado de colores)
     * que facilita seguir una fila con la vista en hojas con muchos datos.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja
     * @param int $fila  Número de fila (base 1).
     * @param int $cols  Cantidad de columnas a colorear.
     * @return void
     */
    private static function colorearFila($hoja, int $fila, int $cols): void
    {
        // Construir el rango "A5:G5" dinámicamente según el número de columnas
        $letraFinal = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cols);
        $rango      = 'A' . $fila . ':' . $letraFinal . $fila;

        $hoja->getStyle($rango)->getFill()
             ->setFillType(Fill::FILL_SOLID)
             ->getStartColor()->setRGB(self::COLOR_FILA_ALT);
    }
}
