# 📦 Inventario — CRUD en PHP

Sistema web de **inventario de productos** con autenticación de usuarios,
hecho en **PHP + MySQL** y empaquetado con **Docker**. Permite crear, listar,
editar y eliminar productos, con login, registro y recuperación de contraseña
por pregunta de seguridad.

Proyecto de la materia **Desarrollo de Aplicaciones Multiplataforma**.

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

## 🚀 Inicio rápido (desde cero)

> Requisitos: **Docker Desktop** y **Git** instalados.
> La instalación detallada por sistema operativo (Windows / macOS / Linux)
> está en **[`docs/guia-instalacion.html`](docs/guia-instalacion.html)**
> (ábrela en el navegador después de clonar).

```bash
# 1. Clonar el proyecto
git clone https://github.com/ra3gar/tarea_crud_php.git
cd tarea_crud_php

# 2. Crear tu archivo de credenciales local
cp .env.example .env
#    editá .env y poné tu propia clave:  DB_PASS=mi_clave_local

# 3. Levantar todo (base + phpMyAdmin + app). La base se crea sola.
docker compose up -d --build

# 4. Abrir en el navegador:
#    App:        http://localhost:8001   (crear cuenta e iniciar sesión)
#    phpMyAdmin: http://localhost:8080   (usuario root, la clave de tu .env)
```

Para apagar todo: `docker compose down` · Para reiniciar la base desde cero:
`docker compose down -v` (borra los datos) y volver a levantar.

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
├── docs/                  Guía de instalación
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
