# 📦 Inventario — CRUD en PHP

Sistema web de **inventario de productos** con autenticación de usuarios,
hecho en **PHP + MySQL** y empaquetado con **Docker**. Permite crear, listar,
editar y eliminar productos, con login, registro y recuperación de contraseña
por pregunta de seguridad.

Proyecto de la materia **Desarrollo de Aplicaciones Multiplataforma**.

> 📖 **Guía de instalación paso a paso** (con instrucciones separadas para
> **Windows, macOS y Linux**): abrí el archivo **`docs/guia-instalacion.html`**
> directamente en tu navegador (doble clic). Es una página independiente que
> **no necesita que el proyecto esté corriendo** — justamente te guía para montarlo
> desde cero.

---

## ✨ Características

- **CRUD completo** de productos (crear, leer, actualizar, eliminar).
- **Autenticación**: login, registro, cierre de sesión y recuperación de
  contraseña por pregunta de seguridad.
- **Seguridad**: consultas con PDO + *prepared statements* (anti inyección SQL),
  contraseñas y respuestas con `password_hash()` (nunca en texto plano),
  y salida escapada con `htmlspecialchars()` (anti XSS).
- **Listo para correr desde cero** con un solo comando (Docker Compose).
- Interfaz propia con indicadores de estado de stock.

## 🧰 Tecnologías

| Capa | Herramienta |
|---|---|
| Lenguaje | PHP 8.5 |
| Base de datos | MySQL 8 |
| Acceso a datos | PDO (prepared statements) |
| Estilos | CSS propio + Bootstrap 5 (local) |
| Entorno | Docker + Docker Compose |
| Administración BD | phpMyAdmin |

---

## 🚀 Puesta en marcha (paso a paso)

**Requisitos previos:** tener instalados **Docker Desktop** y **Git**. Si no
los tenés, abrí la guía **`docs/guia-instalacion.html`**, que explica cómo
instalarlos según tu sistema operativo.

> 💻 **Todos los comandos de abajo se escriben en una terminal:**
> - **Windows:** usá **Git Bash** (se instala junto con Git).
> - **macOS / Linux:** usá la app **Terminal**.

### Paso 1 — Descargar el proyecto
En la terminal, ubicate en la carpeta donde lo quieras guardar y cloná el repositorio:

```bash
git clone https://github.com/ra3gar/tarea_crud_php.git
cd tarea_crud_php
```

### Paso 2 — Preparar las credenciales
El proyecto trae una plantilla `.env.example`. Copiala como `.env` y poné tu propia clave local:

```bash
cp .env.example .env
```

Luego abrí el archivo `.env` (en un editor o en VSCode) y dejá tu contraseña:

```
DB_PASS=mi_clave_local
```

> El `.env` **no se sube a git**: tu contraseña queda solo en tu máquina.

### Paso 3 — Levantar el entorno
Un solo comando construye y enciende la base de datos, phpMyAdmin y la aplicación.
La base de datos y sus tablas **se crean solas** la primera vez:

```bash
docker compose up -d --build
```

> ⏳ La primera vez puede tardar 1–3 minutos (descarga imágenes). Esperá unos
> 30 segundos extra para que MySQL termine de arrancar.

### Paso 4 — Abrir la aplicación
En el navegador entrá a:

- **Aplicación:** http://localhost:8001 → hacé clic en *"Crear cuenta"*, registrate e iniciá sesión.
- **phpMyAdmin** (opcional, para ver la base): http://localhost:8080 → usuario `root`, la clave de tu `.env`.

---

### 🛑 Comandos útiles

```bash
docker compose down        # apagar todo (conserva los datos)
docker compose down -v     # apagar y BORRAR la base (empezar de cero)
docker compose ps          # ver el estado de los contenedores
docker compose logs -f     # ver los registros en vivo
```

---

## 🗂️ Estructura del proyecto

```
tarea_crud_php/
├── index.php              Lista de productos (pantalla principal)
├── crear/editar/eliminar.php   Alta, modificación y baja
├── login/registro/recuperar/logout.php   Autenticación
├── auth.php               Protección de páginas privadas
├── config.php             Conexión PDO a MySQL
├── database/schema.sql    Crea base, tablas y datos de prueba
├── includes/              Encabezado y pie comunes
├── assets/                Estilos, Bootstrap y fuentes (locales)
├── docs/                  Guía de instalación (guia-instalacion.html)
├── Dockerfile             Imagen PHP con drivers de MySQL
└── docker-compose.yml     Levanta todo el entorno
```

## 🔐 Nota de seguridad

Las credenciales **no se versionan**: viven en `.env` y/o `secrets.php`
(ambos ignorados por git). Se incluye `.env.example` como plantilla.

---

## 👥 Integrantes

- (Nombre y apellido)
- (Nombre y apellido)
- (Nombre y apellido)

> _Materia: Desarrollo de Aplicaciones Multiplataforma._
