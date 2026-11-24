# 📦 Inventario — CRUD en PHP (MVC)

Sistema web de **inventario de productos** con autenticación y **roles de
usuario**, hecho en **PHP + MySQL** (MVC a mano, sin framework) y empaquetado
con **Docker**. Permite crear, listar, editar y eliminar productos, administrar
categorías, gestionar usuarios, y trae un tablero con gráficos.

Proyecto de la materia **Desarrollo de Aplicaciones Multiplataforma**.

---

## 📖 Empezá por las guías (toda la ayuda está acá)

Este README es solo un índice. **Las instrucciones completas están en las dos
guías** que viven en la carpeta **`docs/`** del proyecto. Son archivos `.html`:
para abrirlos, entrá a esa carpeta y **hacé doble clic** — se abren en el
navegador y **no** necesitan Docker ni el proyecto corriendo.

```
tarea_crud_php/
└── docs/
    ├── guia-instalacion.html   ← EMPEZÁ POR ESTA
    └── guia-lectura.html
```

- **`docs/guia-instalacion.html` — cómo instalarlo y usarlo.** Requisitos,
  instalación paso a paso (Windows, macOS, Linux), qué es y cuándo configurar el
  `.env`, el rol de Docker, **el usuario y la contraseña para entrar al
  sistema**, cómo conectarse a la base de datos (phpMyAdmin y DBeaver) y el
  desglose de carpetas por capa. Incluye un glosario para quien arranca de cero.
- **`docs/guia-lectura.html` — cómo entender el código.** La organización MVC,
  cómo viaja una petición, las tecnologías usadas y los diagramas. Actualizada
  para la estructura vigente (`app/funciones.php`, `app/autoload.php`,
  `app/Router.php` y los permisos declarados en `routes/web.php`).

### 📑 Documentación técnica (Markdown, en `docs/`)

| Documento | Qué contiene |
|---|---|
| `ARQUITECTURA.md` | MVC simplificado, flujo de ejecución, responsabilidad de cada componente |
| `ESTRUCTURA.md` | Árbol de carpetas y qué hace cada archivo |
| `GUIA_CODIGO.md` | Clases, funciones, métodos, consultas SQL y validaciones |
| `BASE_DATOS.md` | Tablas, claves, relaciones, integridad y consultas importantes |
| `DOCKER.md` | Servicios, puertos, volúmenes, variables y comandos |
| `INSTALACION.md` | Requisitos, puesta en marcha y problemas frecuentes |
| `SEGURIDAD.md` | Cada medida de seguridad y qué problema evita |

### 📘 Manuales e ilustraciones (en `DOCUMENTACION/`)

| Documento | Para quién | Qué contiene |
|---|---|---|
| `MANUAL_TECNICO_CRUD_INVENTARIO.docx` | Desarrolladores y administradores | Arquitectura, código archivo por archivo, base de datos, seguridad, Docker, instalación y resolución de problemas |
| `MANUAL_DE_USUARIO_CRUD_INVENTARIO.docx` | Quien usa el sistema | Cada pantalla y cada procedimiento, paso a paso y con capturas reales |
| `GUIA_RAPIDA_CRUD_INVENTARIO.pdf` | Consulta rápida | Las operaciones más frecuentes, en pocas páginas |

```
DOCUMENTACION/
└── recursos/
    ├── capturas/     15 capturas reales de la aplicación en funcionamiento
    └── diagramas/    7 diagramas: arquitectura, flujo, MVC, ER, Docker, permisos, árbol
```

> ℹ️ Las credenciales de acceso y los pasos de instalación **no** están en este
> README a propósito: viven en la guía de instalación, que es el manual oficial.

---

## ✨ Características

- **CRUD completo** de productos (crear, leer, actualizar, eliminar).
- **Tablero** en la pantalla de productos: métricas + gráfico de barras (valor
  por categoría) y de pastel (unidades por categoría), en **CSS puro, sin
  JavaScript**, y respetan el filtro activo.
- **Categorías como tabla** (se eligen de una lista; relación por clave foránea
  con productos).
- **Autenticación**: login, registro (con nombre y apellido separados), logout y
  recuperación de contraseña por pregunta de seguridad.
- **Gestión de usuarios con roles** (`admin` / `usuario`): solo un admin
  administra usuarios.
- **Seguridad**: PDO + *prepared statements* (anti inyección SQL),
  `password_hash()` (contraseñas nunca en texto), `htmlspecialchars()` (anti XSS)
  y **protección CSRF** en los 13 formularios que modifican información,
  verificada de forma central en el front controller.
- **Permisos en un solo lugar**: cada ruta declara en `routes/web.php` si es
  pública, si requiere sesión o si requiere rol `admin`.

## 🧰 Tecnologías

| Capa | Herramienta |
|---|---|
| Lenguaje | PHP 8.5 (MVC a mano, sin framework) |
| Base de datos | MySQL 8.4 |
| Acceso a datos | PDO (prepared statements) |
| Estilos | CSS propio + Bootstrap 5 (local, sin CDN) |
| Entorno | Docker + Docker Compose |
| Administración BD | phpMyAdmin |

> La estructura de carpetas y la explicación del código están en
> `docs/ESTRUCTURA.md` y `docs/GUIA_CODIGO.md`.

## 🔐 Seguridad y credenciales

Las credenciales de la base **no se versionan**: viven en `.env` (o `secrets.php`
si se monta a mano), ambos ignorados por git; se incluyen `.env.example` y
`secrets.example.php` como plantillas. El código sensible (`app/`, `config/`,
`secrets.php`) vive **fuera** de `public/`, fuera del alcance del navegador.

El usuario y la contraseña para **entrar al sistema** están documentados en la
guía de instalación (`docs/guia-instalacion.html`), no en este README.

---

## 👥 Integrantes

- (Nombre y apellido)
- (Nombre y apellido)
- (Nombre y apellido)

> _Materia: Desarrollo de Aplicaciones Multiplataforma._

---

Diseñado y mantenido por **Ricardo Alvarez**.
