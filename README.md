# 📦 Inventario — CRUD en PHP

Sistema web de **inventario de productos** con autenticación de usuarios,
hecho en **PHP + MySQL** y empaquetado con **Docker**. Permite crear, listar,
editar y eliminar productos, con login, registro y recuperación de contraseña
por pregunta de seguridad.

Proyecto de la materia **Desarrollo de Aplicaciones Multiplataforma**.

> 📖 **Dos guías**, las dos se abren con doble clic en el navegador y no
> necesitan que el proyecto esté corriendo:
>
> - **`docs/guia-instalacion.html`** — cómo montarlo de cero, con instrucciones
>   por Windows, macOS y Linux, y una sección que explica qué hace Docker por
>   dentro.
> - **`docs/guia-lectura.html`** — cómo está organizado el código: qué hace cada
>   archivo y cómo viaja una petición desde el navegador hasta la base.

---

## ✨ Características

- **CRUD completo** de productos (crear, leer, actualizar, eliminar).
- **Autenticación**: login, registro, cierre de sesión y recuperación de
  contraseña por pregunta de seguridad.
- **Seguridad**: consultas con PDO + *prepared statements* (anti inyección SQL),
  contraseñas y respuestas con `password_hash()` (nunca en texto plano),
  y salida escapada con `htmlspecialchars()` (anti XSS).
- Interfaz propia con indicadores de estado de stock.

## 🧰 Tecnologías

| Capa | Herramienta |
|---|---|
| Lenguaje | PHP 8.5 |
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

Eso es todo. La primera vez tarda unos minutos porque descarga MySQL y
phpMyAdmin y construye la imagen de PHP; después arranca en segundos.

La base se crea sola con las tablas y **5 productos de prueba**, pero sin
usuarios: el primero lo creás vos desde "Crear cuenta".

> 🔑 **La contraseña la inventás vos** y va en un solo lugar: la línea
> `DB_PASS` del archivo `.env`. Ese archivo está en el `.gitignore`, así que
> nunca llega a GitHub.

### 🐳 Qué levanta ese comando

| Contenedor | Qué es | Dónde |
|---|---|---|
| `tarea_crud_mysql` | La base de datos: productos y usuarios | solo interno |
| `tarea_crud_php` | Apache + PHP: ejecuta las páginas | http://localhost:8001 |
| `tarea_crud_phpmyadmin` | Panel para mirar la base (opcional) | http://localhost:8081 |

Más la red privada `tarea_crud_red` y el volumen `tarea_crud_mysql_datos`,
donde la base guarda lo que escribe.

Todo lleva el prefijo `tarea_crud_` a propósito: el proyecto no comparte
contenedor, red ni volumen con nada más, así que se puede levantar y apagar
sin tocar otros proyectos que tengas en Docker.

### 🛠️ Comandos útiles

```bash
docker compose up -d              # encender
docker compose down               # apagar (los datos se conservan)
docker compose down -v            # apagar y BORRAR la base (empezar de cero)
docker compose up -d --build      # rearmar la imagen de PHP
docker ps                         # ver qué está corriendo
docker compose logs php           # ver qué dice la aplicación
docker compose logs mysql         # ver qué dice la base de datos
docker compose restart php        # reiniciar solo la aplicación
```

Para editar código no hace falta reconstruir nada: la carpeta está montada
dentro del contenedor, así que cambiás un `.php`, recargás, y ya está.

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
├── docs/                  Guía de instalación y guía de lectura
├── .env.example           Plantilla de la contraseña (creás tu .env)
├── secrets.example.php    Plantilla alternativa, para montar a mano
├── Dockerfile             Imagen PHP con drivers de MySQL
└── docker-compose.yml     Define los tres contenedores
```

## 🔐 Nota de seguridad

Las credenciales **no se versionan**: viven en `.env` (o en `secrets.php` si
montás el proyecto a mano), y los dos están ignorados por git. Se incluyen
`.env.example` y `secrets.example.php` como plantillas.

El archivo `apache-deny.conf` además impide que Apache sirva por el navegador
archivos sensibles o internos: `.env`, `secrets.php`, los `.sql` y los `.md`.

---

## 👥 Integrantes

- (Nombre y apellido)
- (Nombre y apellido)
- (Nombre y apellido)

> _Materia: Desarrollo de Aplicaciones Multiplataforma._

---

Diseñado y mantenido por **Ricardo Alvarez**.
