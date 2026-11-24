# Documentación archivada

Lo que hay en esta carpeta **describe una versión anterior del proyecto** y se
conserva solo como referencia histórica. No la uses para entender el código
actual: te va a confundir, porque menciona archivos que ya no existen.

## `GUIA_TECNICA-arquitectura-anterior/`

Era la guía técnica del proyecto antes de la reestructuración a MVC. Su
documento Word, sus 11 diagramas y sus 3 anexos describen una organización del
código que ya fue reemplazada.

### Qué nombra que ya no existe

| Lo que dice la guía archivada | Lo que hay ahora |
|---|---|
| `app/Core/autoload.php` | `app/autoload.php` |
| `app/Core/Router.php` | `app/Router.php` |
| `app/Core/Controller.php` (clase base con `view()`) | No hay clase base: la función `vista()` en `app/funciones.php` |
| `app/Core/Database.php` (singleton PDO) | La función `db()` en `app/funciones.php`, con `static $pdo` |
| `app/Core/helpers.php` | `app/funciones.php` |
| `app/Middleware/Auth.php` | La función `proteger_sesion()` |
| `app/Middleware/Admin.php` | La función `proteger_admin()` |
| `includes/header.php` · `includes/footer.php` | `app/Views/layouts/header.php` y `footer.php` |

Además, el control de acceso ya no funciona con middlewares encadenados: el
permiso se declara como cuarto parámetro en `routes/web.php` y el Router lo
aplica antes de instanciar el controlador.

## Dónde está la documentación vigente

| Documento | Para quién |
|---|---|
| `DOCUMENTACION/MANUAL_TECNICO_CRUD_INVENTARIO.docx` | Desarrolladores y administradores |
| `DOCUMENTACION/MANUAL_DE_USUARIO_CRUD_INVENTARIO.docx` | Quien usa el sistema |
| `DOCUMENTACION/GUIA_RAPIDA_CRUD_INVENTARIO.pdf` | Consulta rápida |
| `docs/*.md` | Documentación técnica en Markdown |
| `docs/guia-instalacion.html` | Manual oficial de instalación y uso |
| `docs/guia-lectura.html` | Recorrido didáctico por el código |

## Si querés borrar esta carpeta

Nada del proyecto la referencia, así que se puede eliminar sin romper ningún
enlace. Se archivó en lugar de borrarse para no perder trabajo previo.
