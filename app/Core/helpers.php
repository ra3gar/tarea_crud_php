<?php
// =====================================================================
//  app/Core/helpers.php — Ayudantes cortos que usan vistas y controladores.
//  Se cargan una vez en el front controller y quedan disponibles en todos
//  lados.
// =====================================================================

/**
 * Carpeta base desde la que el navegador ve a index.php.
 *
 * Si el sitio esta en una subcarpeta (o en la raiz), lo detecta solo, asi
 * los enlaces no se rompen segun donde se publique el proyecto.
 *
 * @return string Ruta base sin barra final (ej. "" en la raiz, "/inventario"
 *                en una subcarpeta).
 */
function base_path(): string {
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
}

/**
 * Arma un enlace hacia una ruta de la app.
 *
 * Toda la navegacion pasa por index.php, asi funciona en cualquier servidor
 * sin configuracion extra.
 *
 * @param string $ruta Ruta interna sin barra inicial (ej. "productos/crear").
 * @return string      URL completa lista para usar en un href o un action.
 */
function url(string $ruta = ''): string {
    return base_path() . '/index.php/' . ltrim($ruta, '/');
}

/**
 * Arma la direccion de un archivo estatico dentro de public/ (css, fuentes).
 *
 * @param string $ruta Ruta del recurso (ej. "assets/css/estilo.css").
 * @return string      URL completa del archivo estatico.
 */
function asset(string $ruta): string {
    return base_path() . '/' . ltrim($ruta, '/');
}

/**
 * Escapa texto antes de imprimirlo en una vista. Es la regla 3 (anti-XSS)
 * en una sola palabra corta.
 *
 * ENT_QUOTES tambien escapa comillas, por si el texto va dentro de un atributo.
 *
 * @param string|null $texto Texto a mostrar (acepta null, lo trata como "").
 * @return string            Texto seguro para insertar dentro del HTML.
 */
function e(?string $texto): string {
    return htmlspecialchars((string) $texto, ENT_QUOTES);
}
