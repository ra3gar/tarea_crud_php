# Inventario de archivos — Tarea_CRUD_PHP

Anexo de la *Guia tecnica de arquitectura, estructura y codigo fuente* (v1.0, 2026-11-16).

Clasificacion de los 56 elementos del repositorio. La columna **Listado** indica si el
codigo integro del archivo aparece en la Parte IV del documento.

| Archivo / ubicacion | Tipo | Lenguaje | Modulo | Responsabilidad | Participacion en el flujo | Listado |
|---|---|---|---|---|---|---|
| `public/index.php` | Codigo fuente propio | PHP | Entrada | Front controller: sesion, autoload, rutas y despacho | **Paso 1** de toda peticion | **Si** |
| `public/.htaccess` | Configuracion | Apache | Entrada | URLs limpias opcionales | Antes de index.php, si hay mod_rewrite | **Si** |
| `routes/web.php` | Codigo fuente propio | PHP | Entrada | Declara las 22 rutas | Paso 2: llena el Router | **Si** |
| `app/Core/autoload.php` | Codigo fuente propio | PHP | Nucleo | Traduce clase → archivo | Transversal: en toda la peticion | **Si** |
| `app/Core/helpers.php` | Codigo fuente propio | PHP | Nucleo | `base_path` `url` `asset` `e` | Transversal | **Si** |
| `app/Core/Router.php` | Codigo fuente propio | PHP | Nucleo | Elige el controlador | Paso 3: despacho | **Si** |
| `app/Core/Controller.php` | Codigo fuente propio | PHP | Nucleo | Clase base: `vista` y `redirigir` | Entre controlador y vista | **Si** |
| `app/Core/Database.php` | Codigo fuente propio | PHP | Nucleo | Conexion PDO unica | Al primer acceso a datos | **Si** |
| `app/Middleware/Auth.php` | Codigo fuente propio | PHP | Seguridad | Exige sesion | Antes del controlador, en rutas privadas | **Si** |
| `app/Middleware/Admin.php` | Codigo fuente propio | PHP | Seguridad | Exige rol admin | En el constructor de `UsuarioController` | **Si** |
| `app/Controllers/ProductoController.php` | Codigo fuente propio | PHP | Inventario | Coordina el CRUD y el tablero | Paso 4 en 8 rutas | **Si** |
| `app/Controllers/CategoriaController.php` | Codigo fuente propio | PHP | Categorias | Coordina categorias | Paso 4 en 3 rutas | **Si** |
| `app/Controllers/AuthController.php` | Codigo fuente propio | PHP | Autenticacion | Entrar, registrar, recuperar, salir | Paso 4 en 7 rutas publicas | **Si** |
| `app/Controllers/UsuarioController.php` | Codigo fuente propio | PHP | Usuarios | CRUD de cuentas y roles | Paso 4 en 7 rutas, solo admin | **Si** |
| `app/Models/Producto.php` | Codigo fuente propio | PHP | Inventario | 11 consultas de `productos` + validacion | Paso 5: acceso a datos | **Si** |
| `app/Models/Categoria.php` | Codigo fuente propio | PHP | Categorias | 6 consultas de `categorias` | Paso 5 | **Si** |
| `app/Models/Usuario.php` | Codigo fuente propio | PHP | Autenticacion / Usuarios | 12 consultas de `usuarios` | Paso 5 | **Si** |
| `app/Views/layouts/header.php` | Codigo fuente propio | PHP+HTML | Presentacion | Cabecera, avisos y menu | Paso 6, en las 12 paginas | **Si** |
| `app/Views/layouts/footer.php` | Codigo fuente propio | HTML | Presentacion | Pie y cierre del documento | Paso 6 | **Si** |
| `app/Views/productos/index.php` | Codigo fuente propio | PHP+HTML | Inventario | Listado + tablero con 2 graficos | Paso 6 | **Si** |
| `app/Views/productos/_campos.php` | Codigo fuente propio | PHP+HTML | Inventario | Campos compartidos del formulario | Dentro de crear y editar | **Si** |
| `app/Views/productos/crear.php` | Codigo fuente propio | PHP+HTML | Inventario | Formulario de alta | Paso 6 | **Si** |
| `app/Views/productos/editar.php` | Codigo fuente propio | PHP+HTML | Inventario | Formulario de edicion | Paso 6 | **Si** |
| `app/Views/productos/eliminar.php` | Codigo fuente propio | PHP+HTML | Inventario | Confirmacion de borrado | Paso 6 | **Si** |
| `app/Views/categorias/index.php` | Codigo fuente propio | PHP+HTML | Categorias | Listado con alta y borrado | Paso 6 | **Si** |
| `app/Views/auth/login.php` | Codigo fuente propio | PHP+HTML | Autenticacion | Formulario de entrada | Paso 6, publico | **Si** |
| `app/Views/auth/registro.php` | Codigo fuente propio | PHP+HTML | Autenticacion | Formulario de alta de cuenta | Paso 6, publico | **Si** |
| `app/Views/auth/recuperar.php` | Codigo fuente propio | PHP+HTML | Autenticacion | Recuperacion en dos pasos | Paso 6, publico | **Si** |
| `app/Views/usuarios/index.php` | Codigo fuente propio | PHP+HTML | Usuarios | Tabla de usuarios | Paso 6, solo admin | **Si** |
| `app/Views/usuarios/crear.php` | Codigo fuente propio | PHP+HTML | Usuarios | Alta desde el panel | Paso 6, solo admin | **Si** |
| `app/Views/usuarios/editar.php` | Codigo fuente propio | PHP+HTML | Usuarios | Perfil, rol y reseteo de clave | Paso 6, solo admin | **Si** |
| `config/config.php` | Configuracion | PHP | Nucleo | Decide el origen de credenciales | Al crear la conexion | **Si** |
| `public/assets/css/estilo.css` | Recurso frontend | CSS | Presentacion | Tema propio y los dos graficos | En el navegador | **Si** |
| `database/schema.sql` | Base de datos | SQL | Datos | Crea base, 3 tablas, datos y la FK | Al crear el volumen de MySQL | **Si** |
| `database/migracion-01-usuarios-roles.sql` | Base de datos | SQL | Datos | Agrega nombre, apellido y rol | Manual, sobre base existente | **Si** |
| `database/migracion-02-fk-categoria.sql` | Base de datos | SQL | Datos | Normaliza y agrega la clave foranea | Manual, una sola vez | **Si** |
| `database/seed-categorias.sql` | Base de datos | SQL | Datos | Recarga las 4 categorias | Manual, opcional | **Si** |
| `Dockerfile` | Configuracion | Dockerfile | Infraestructura | Imagen PHP con drivers y rewrite | Al construir | **Si** |
| `docker-compose.yml` | Configuracion | YAML | Infraestructura | 3 contenedores, red y volumen | Al levantar | **Si** |
| `docker/apache.conf` | Configuracion | Apache | Infraestructura | `DocumentRoot` = `public/` | En el arranque de Apache | **Si** |
| `apache-deny.conf` | Configuracion | Apache | Infraestructura | Niega archivos sensibles | En cada peticion | **Si** |
| `.env.example` | Plantilla | env | Infraestructura | Plantilla de `DB_PASS` | No se ejecuta | **Si** |
| `secrets.example.php` | Plantilla | PHP | Infraestructura | Plantilla de credenciales | No se ejecuta | **Si** |
| `.gitignore` | Configuracion | git | Repositorio | Excluye secretos y documentacion interna | No se ejecuta | **Si** |
| `CLAUDE.md` | Documentacion | Markdown | Repositorio | Reglas para asistentes de IA | No se ejecuta | **Si** |
| `.claude/settings.json` | Configuracion | JSON | Repositorio | Desactiva la atribucion de IA | No se ejecuta | **Si** |
| `README.md` | Documentacion | Markdown | Repositorio | Indice hacia las guias | No se ejecuta | No |
| `docs/guia-instalacion.html` | Documentacion | HTML | Repositorio | Manual oficial: pasos y credenciales | No se ejecuta | No |
| `docs/guia-lectura.html` | Documentacion | HTML | Repositorio | Guia de lectura del codigo | No se ejecuta | No |
| `includes/header.php` | **Codigo muerto** | PHP+HTML | — | Ninguna: resto pre-MVC | **No participa** | **Si** |
| `includes/footer.php` | **Codigo muerto** | PHP+HTML | — | Ninguna: resto pre-MVC | **No participa** | **Si** |
| `public/assets/css/bootstrap.min.css` | Dependencia externa | CSS | Presentacion | Bootstrap 5.3.3 | En el navegador | No |
| `public/assets/fonts/*.woff2` | Recurso binario | — | Presentacion | Tipografias Inter y Space Grotesk | En el navegador | No |
| `public/assets/fonts/fuentes.css` | Recurso frontend | CSS | Presentacion | Declaraciones `@font-face` | En el navegador | No |
| `.env / secrets.php` | **Sensible, excluido** | env / PHP | Infraestructura | Credenciales reales | Leidos por `config.php` | No |
| `DOCUMENTACION_MD/` | Documentacion interna | Markdown | — | Memoria de trabajo, fuera de git | No se ejecuta | No |

## Elementos no reproducidos y motivo

| Elemento | Motivo |
|---|---|
| `public/assets/css/bootstrap.min.css` | Dependencia externa (Bootstrap 5.3.3), minificada y de autoria ajena. |
| `public/assets/fonts/*.woff2` | Archivos binarios de tipografia: no tienen codigo fuente legible. |
| `public/assets/fonts/fuentes.css` | Declaraciones `@font-face` generadas; sin logica del sistema. |
| `docs/guia-instalacion.html`, `docs/guia-lectura.html` | Documentacion para el usuario final, no logica de la aplicacion. Se citan como referencia. |
| `README.md` | Indice que apunta a las guias; su contenido se resume en el documento. |
| `.env`, `secrets.php` | Contienen la contrasena real de la base. Se documentan sus plantillas. |
| `DOCUMENTACION_MD/` | Memoria de trabajo del proyecto, ignorada por git. |
| `.git/` | Metadatos del control de versiones. |

## Resumen

- Elementos inventariados: **56**
- Archivos con listado integro en la Parte IV: **48**
- Lineas de codigo propio reproducidas: **2850**
- Archivos de codigo muerto identificados: **2** (`includes/header.php`, `includes/footer.php`)
