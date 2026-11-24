<?php
// =====================================================================
//  app/Modules/Exportar/PdfExporter.php — Generador de archivos PDF
//
//  Ubicación en la arquitectura por módulos:
//    app/Modules/Exportar/
//      PdfExporter.php        <- este archivo (servicio PDF)
//      ExcelExporter.php      <- servicio Excel
//      ExportarController.php <- controlador del módulo
//      Views/                 <- plantillas HTML que se renderizan a PDF
//
//  Qué hace:
//    Envuelve la biblioteca Dompdf para convertir una plantilla HTML
//    (ubicada en Views/) en un PDF y enviarlo directamente al navegador
//    como descarga, sin escribir archivos temporales en disco.
//
//  Por qué Dompdf:
//    - Licencia LGPL: compatible con proyectos académicos.
//    - Entrada: HTML + CSS estándar → salida: PDF. Sin configuración de
//      servidor extra (como wkhtmltopdf que necesita un binario externo).
//    - Mantenida activamente (v3.x, PHP 8.1+).
//
//  Cómo funciona el flujo:
//    1. ExportarController llama PdfExporter::generar('productos', $datos).
//    2. Este clase carga la vista HTML correspondiente con ob_start() /
//       ob_get_clean() para capturar el HTML sin mostrarlo en pantalla.
//    3. Dompdf convierte el HTML a PDF en memoria.
//    4. Se envían las cabeceras HTTP correctas y el PDF llega al navegador
//       como descarga (Content-Disposition: attachment).
//
//  SEGURIDAD:
//    El nombre del archivo PDF se construye aquí, no viene del usuario,
//    para evitar inyección de nombres de archivo.
// =====================================================================

namespace App\Modules\Exportar;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Servicio de exportación a PDF.
 *
 * Clase sin estado (todos los métodos estáticos): no necesita instanciarse.
 * El controlador la usa como herramienta, no como objeto con ciclo de vida.
 */
class PdfExporter
{
    /**
     * Genera un PDF a partir de una plantilla HTML y lo envía al navegador.
     *
     * El método construye la ruta a la plantilla, la renderiza capturando
     * su salida, se la entrega a Dompdf y luego envía el PDF como descarga.
     *
     * @param string               $plantilla Nombre de la vista sin extensión
     *                                        (ej. 'pdf_productos'). Debe existir
     *                                        en app/Modules/Exportar/Views/.
     * @param array<string, mixed> $datos     Variables que necesita la plantilla
     *                                        (ej. ['productos' => [...]]).
     * @param string               $nombreArchivo Nombre del .pdf que descarga el usuario.
     * @return void  Termina con exit() para evitar que Apache añada basura.
     */
    public static function generar(string $plantilla, array $datos, string $nombreArchivo): void
    {
        // ---- 1. Renderizar la plantilla HTML --------------------------------
        // extract() inyecta las claves del arreglo como variables locales.
        // Así la vista puede usar $productos, $categorias, etc. directamente.
        extract($datos, EXTR_SKIP);

        // La vista está en el mismo módulo, una subcarpeta más abajo.
        // __DIR__ apunta a app/Modules/Exportar/, así la ruta es siempre correcta
        // sin importar desde dónde se llame al método.
        $rutaVista = __DIR__ . '/Views/' . $plantilla . '.php';

        if (!is_file($rutaVista)) {
            // Si la plantilla no existe, es un error de programación, no del usuario.
            http_response_code(500);
            echo "Error interno: plantilla PDF '$plantilla' no encontrada.";
            exit();
        }

        // ob_start() / ob_get_clean() captura todo lo que echo/print produzcan
        // dentro del include sin enviarlo al navegador todavía.
        ob_start();
        include $rutaVista;
        $html = ob_get_clean();

        // ---- 2. Configurar Dompdf -------------------------------------------
        $opciones = new Options();

        // isRemoteEnabled: false → no carga URLs externas (fuentes de Google,
        // imágenes remotas). Mejora la seguridad y hace el PDF reproducible
        // sin conexión a internet.
        $opciones->set('isRemoteEnabled', false);

        // defaultFont: fuente de respaldo cuando el HTML no especifica ninguna.
        $opciones->set('defaultFont', 'helvetica');

        // chroot: restringe el sistema de archivos que Dompdf puede leer.
        // Solo se permitirá acceder a archivos dentro de la carpeta del módulo.
        $opciones->set('chroot', __DIR__);

        $dompdf = new Dompdf($opciones);

        // ---- 3. Cargar el HTML y renderizar el PDF --------------------------
        $dompdf->loadHtml($html, 'UTF-8');

        // Tamaño de hoja: Carta (Letter), orientación vertical.
        // 'letter' = 8.5 × 11 pulgadas, el estándar usado en El Salvador.
        $dompdf->setPaper('letter', 'portrait');

        // render() convierte el HTML a PDF en memoria (no escribe archivos).
        $dompdf->render();

        // ---- 4. Enviar el PDF al navegador ----------------------------------
        // Content-Type: le dice al navegador que recibirá un PDF.
        // Content-Disposition: attachment → fuerza la descarga en vez de
        //   mostrarlo en el visor del navegador (más útil para informes).
        // filename: el nombre con que se guardará en el disco del usuario.
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

        // Limpiar cualquier salida anterior (espacios en blanco, BOM) que
        // pudiera corromper el PDF si algún archivo PHP emitió algo antes.
        if (ob_get_level()) {
            ob_end_clean();
        }

        // output() devuelve el contenido binario del PDF.
        echo $dompdf->output();

        // exit() es importante: evita que Apache o PHP añadan HTML de error
        // al final del PDF, lo que lo corrompería.
        exit();
    }
}
