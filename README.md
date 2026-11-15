# 📦 Inventario — CRUD en PHP (MVC)

Sistema web de **inventario de productos** con autenticación y **roles de
usuario**, hecho en **PHP + MySQL** (MVC a mano, sin framework) y empaquetado
con **Docker**. Permite crear, listar, editar y eliminar productos, administrar
categorías, gestionar usuarios, y trae un tablero con gráficos.

Proyecto de la materia **Desarrollo de Aplicaciones Multiplataforma**.

## 📖 Archivos de ayuda (dónde están y cómo abrirlos)

Las guías viven dentro de la **carpeta `docs/`** del proyecto (una vez que lo
clonaste o descargaste de GitHub). Son archivos `.html`: para abrirlos, entrá a
esa carpeta y **hacé doble clic** en el archivo — se abren en el navegador y
**no** necesitan que el proyecto esté corriendo ni Docker encendido.

```
tarea_crud_php/
└── docs/
    ├── guia-instalacion.html   ← empezá por esta
    └── guia-lectura.html
```

- **`docs/guia-instalacion.html`** — cómo montarlo de cero (Windows, macOS,
  Linux), cuándo configurar el `.env`, el rol de Docker, cómo conectarse a la
  base de datos (phpMyAdmin y DBeaver) y el desglose de carpetas por capa
  (frontend / backend / base de datos).
- **`docs/guia-lectura.html`** — cómo está organizado el código MVC: qué es cada
  carpeta, cómo viaja una petición desde el navegador hasta la base, las
  tecnologías usadas y los diagramas (comunicación y esquema de la base).

---

## ✨ Características

- **CRUD completo** de productos (crear, leer, actualizar, eliminar).
- **Tablero** en la pantalla de productos: métricas + gráfico de barras (valor
  por categoría) y de pastel (unidades por categoría). Hechos en **CSS puro,
  sin JavaScript**, y respetan el filtro activo.
- **Categorías como tabla**: se eligen de una lista y se administran en su
  propia ventana (no se escriben a mano).
- **Autenticación**: login, registro (con **nombre y apellido separados**),
  cierre de sesión y recuperación de contraseña por pregunta de seguridad.
- **Gestión de usuarios con roles** (`admin` / `usuario`): solo un admin puede
  listar, crear, editar el rol, resetear contraseñas y eliminar usuarios. El
  **primer usuario registrado queda admin**.
- **Seguridad** (3 reglas): PDO + *prepared statements* (anti inyección SQL),
  `password_hash()` para contraseñas y respuestas (nunca en texto plano), y
  salida escapada con `htmlspecialchars()` (anti XSS). Más guardias de sesión
  y de rol.

## 🧰 Tecnologías

| Capa | Herramienta |
|---|---|
| Lenguaje | PHP 8.5 (MVC a mano, sin framework) |
| Base de datos | MySQL 8.4 |
| Acceso a datos | PDO (prepared statements) |
| Estilos | CSS propio + Bootstrap 5 (local, sin CDN) |
| Entorno | Docker + Docker Compose |
| Administración BD | phpMyAdmin |

---

## 🚀 Puesta en marcha

**Requisitos:** **Docker Desktop** (abierto) y **Git**.

```bash
# 1. Bajar el proyecto
git clone https://github.com/ra3gar/tarea_crud_php.git
cd tarea_crud_php

# 2. Crear tu .env y poner una contraseña inventada por vos en DB_PASS
cp .env.example .env

# 3. Levantar todo
docker compose up -d --build

# 4. Abrir http://localhost:8001  ->  "Crear cuenta" e iniciar sesión
```

La primera vez tarda unos minutos (descarga MySQL y phpMyAdmin y construye la
imagen de PHP); después arranca en segundos.

La base se crea sola con las tablas, **5 productos de prueba** y un **usuario
administrador inicial** (sembrado desde `schema.sql`):

| Rol | Usuario | Contraseña |
|---|---|---|
| Administrador | `admin` | `Inicio01a` |

Podés entrar con ese `admin` para gestionar usuarios, o crear tu propia cuenta
desde "Crear cuenta" (será un usuario normal, porque ya existe un admin).

> Es una credencial por defecto para fines educativos; en un sistema real lo
> primero sería cambiarla.

> 🔑 **La contraseña la inventás vos** y va en un solo lugar: la línea `DB_PASS`
> del archivo `.env`, que está en el `.gitignore` y nunca llega a GitHub.

### 🔄 ¿Ya tenías la base de una versión anterior?

Si ya tenías datos cargados de antes de los roles, en vez de empezar de cero
corré **una sola vez** la migración, que agrega las columnas `nombre`,
`apellido` y `rol` **sin borrar nada** (y hace admin al usuario más antiguo):

```bash
docker exec -i tarea_crud_mysql mysql -u root -p tarea_crud_db < database/migracion-01-usuarios-roles.sql
```

> Las instalaciones nuevas no la necesitan: `schema.sql` ya trae esas columnas.

### 🐳 Qué levanta ese comando

| Contenedor | Qué es | Dónde |
|---|---|---|
| `tarea_crud_mysql` | La base de datos: productos, categorías y usuarios | solo interno |
| `tarea_crud_php` | Apache + PHP: ejecuta la app (front controller MVC) | http://localhost:8001 |
| `tarea_crud_phpmyadmin` | Panel para mirar la base (opcional) | http://localhost:8081 |

Más la red privada `tarea_crud_red` y el volumen `tarea_crud_mysql_datos`.
Todo lleva el prefijo `tarea_crud_` a propósito: el proyecto no comparte
contenedor, red ni volumen con nada más.

### 🔌 Conectarse a la base de datos

Dos formas de mirar o consultar la base:

- **Navegador (phpMyAdmin):** `http://localhost:8081` → usuario `root`, contraseña = tu `DB_PASS` del `.env`. El servidor ya viene configurado.
- **Gestor externo (DBeaver, MySQL Workbench…):** conexión MySQL con host `localhost`, **puerto `3307`**, base `tarea_crud_db`, usuario `root`, contraseña = tu `DB_PASS`. (El puerto `3307` lo publica `docker-compose.yml`; si venías de antes, corré `docker compose up -d` una vez para aplicarlo.)

### 🛠️ Comandos útiles

```bash
docker compose up -d              # encender
docker compose down               # apagar (los datos se conservan)
docker compose down -v            # apagar y BORRAR la base (empezar de cero)
docker compose up -d --build      # rearmar la imagen de PHP
docker compose logs php           # ver qué dice la aplicación
docker compose logs mysql         # ver qué dice la base de datos
```

Para editar código no hace falta reconstruir nada: la carpeta está montada en
el contenedor, así que cambiás un `.php`, recargás, y listo.

---

## 🗂️ Estructura del proyecto (MVC)

```
tarea_crud_php/
├── public/                 Raíz web: lo ÚNICO que el navegador abre directo
│   ├── index.php           Front controller: toda petición entra acá
│   └── assets/             Estilos, Bootstrap y fuentes (locales)
├── app/                    El código del servidor (backend)
│   ├── Core/               Motor: autoload, helpers, Database, Router, Controller
│   ├── Controllers/        Reciben la petición y eligen la vista
│   ├── Models/             Hablan con la base (Producto, Categoria, Usuario)
│   ├── Middleware/         Guardias: Auth (sesión) y Admin (rol)
│   └── Views/              Plantillas que arman el HTML (una carpeta por área)
├── routes/web.php          Tabla de rutas del sitio
├── config/config.php       De dónde salen las credenciales (env o secrets.php)
├── database/               schema.sql + migraciones + seeds
├── docs/                   Guía de instalación y guía de lectura
├── .env.example            Plantilla de la contraseña (creás tu .env)
├── secrets.example.php     Plantilla alternativa, para montar a mano
├── Dockerfile              Imagen PHP con drivers de MySQL
└── docker-compose.yml      Define los tres contenedores
```

## 🔐 Nota de seguridad

Las credenciales **no se versionan**: viven en `.env` (o en `secrets.php` si
montás a mano), los dos ignorados por git; se incluyen `.env.example` y
`secrets.example.php` como plantillas.

El código sensible (`app/`, `config/`, `secrets.php`) vive **fuera** de
`public/`, así que el navegador físicamente no lo alcanza. Además,
`apache-deny.conf` impide servir archivos internos (`.env`, `secrets.php`,
`.sql`, `.md`).

---

## 👥 Integrantes

- (Nombre y apellido)
- (Nombre y apellido)
- (Nombre y apellido)

> _Materia: Desarrollo de Aplicaciones Multiplataforma._

---

Diseñado y mantenido por **Ricardo Alvarez**.
