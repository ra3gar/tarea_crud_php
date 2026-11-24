# Estructura de carpetas y archivos

> **Diagrama**: `DOCUMENTACION/recursos/diagramas/07-arbol-directorios.png`
> muestra este mismo arbol marcando que es publico y que es privado.

```
Tarea_CRUD_PHP/
├── app/                      La aplicacion (NO accesible por el navegador)
│   ├── autoload.php          Carga automatica de clases
│   ├── funciones.php         Ayudas: enlaces, escape, CSRF, permisos, db()
│   ├── Router.php            Enrutador
│   ├── Controllers/          Controladores
│   ├── Models/               Modelos (SQL con PDO)
│   └── Views/                Vistas (HTML)
├── config/
│   └── config.php            De donde salen las credenciales
├── database/                 Esquema, migraciones y seeds
├── docker/
│   └── apache.conf           Apache sirve solo public/
├── docs/                     Documentacion tecnica (Markdown) + las dos guias HTML
├── DOCUMENTACION/            Manuales en Word/PDF, diagramas y capturas
│   ├── MANUAL_TECNICO_CRUD_INVENTARIO.docx
│   ├── MANUAL_DE_USUARIO_CRUD_INVENTARIO.docx
│   ├── GUIA_RAPIDA_CRUD_INVENTARIO.pdf
│   ├── recursos/
│   │   ├── capturas/         Capturas reales de la aplicacion
│   │   └── diagramas/        Diagramas de arquitectura, MVC, ER y Docker
│   └── _archivo/             Documentacion de la version anterior (ver su LEEME.md)
├── public/                   RAIZ WEB: lo unico que el navegador alcanza
│   ├── index.php             Front controller
│   ├── .htaccess             URLs limpias (opcional)
│   └── assets/               CSS y fuentes locales
├── routes/
│   └── web.php               Tabla de rutas y permisos
├── .env.example              Plantilla de la clave local
├── apache-deny.conf          Niega servir archivos sensibles
├── docker-compose.yml        Los tres servicios
├── Dockerfile                Imagen PHP + Apache + drivers
├── secrets.example.php       Plantilla alternativa a .env
└── README.md                 Punto de entrada
```

## Por que `app/` esta fuera de `public/`

Apache tiene como raiz web `public/` (ver `docker/apache.conf`). Los archivos de
`app/`, `config/` y `secrets.php` no estan en esa carpeta, asi que **no se
pueden pedir por URL**. No depende de una regla que alguien pueda desactivar:
simplemente no estan en el camino.

## Archivo por archivo

### Raiz web — `public/`
| Archivo | Que hace |
|---|---|
| `index.php` | Front controller: sesion, ayudas, CSRF, despacho |
| `.htaccess` | Si hay `mod_rewrite`, permite URLs sin `index.php`. Si no, se ignora |
| `assets/css/bootstrap.min.css` | Bootstrap 5.3.3 local (sin CDN) |
| `assets/css/estilo.css` | Estilos propios |
| `assets/fonts/` | Inter y Space Grotesk locales + `fuentes.css` |

### Aplicacion — `app/`
| Archivo | Que hace |
|---|---|
| `autoload.php` | `App\X\Y` -> `app/X/Y.php` |
| `funciones.php` | 12 funciones en 4 grupos (enlaces, seguridad, permisos, datos/MVC) |
| `Router.php` | Tabla de rutas, comparacion por segmentos, permisos |
| `Controllers/AuthController.php` | Login, registro, recuperacion, logout |
| `Controllers/ProductoController.php` | CRUD de productos y tablero |
| `Controllers/CategoriaController.php` | Listar, crear y borrar categorias |
| `Controllers/UsuarioController.php` | Gestion de usuarios (solo admin) |
| `Models/Producto.php` | Consultas de productos, filtros y metricas |
| `Models/Categoria.php` | Consultas de categorias |
| `Models/Usuario.php` | Consultas de usuarios y recuperacion |
| `Views/layouts/header.php` | Cabecera, menu y avisos |
| `Views/layouts/footer.php` | Pie y cierre del HTML |
| `Views/productos/_campos.php` | Campos compartidos por crear y editar |
| `Views/productos/index.php` | Listado + tablero + filtros |
| `Views/productos/crear.php` · `editar.php` · `eliminar.php` | Formularios |
| `Views/categorias/index.php` | Lista y alta de categorias |
| `Views/auth/login.php` · `registro.php` · `recuperar.php` | Pantallas de acceso |
| `Views/usuarios/index.php` · `crear.php` · `editar.php` | Panel de usuarios |

### Base de datos — `database/`
| Archivo | Que hace |
|---|---|
| `schema.sql` | Crea base, tablas, FK y datos de prueba. Re-ejecutable |
| `migracion-01-usuarios-roles.sql` | Migracion de usuarios y roles |
| `migracion-02-fk-categoria.sql` | Migracion de la clave foranea de categoria |
| `seed-categorias.sql` | Categorias iniciales |
