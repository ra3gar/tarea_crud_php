# 📦 Inventario — CRUD en PHP

Sistema web de **inventario de productos** con autenticación de usuarios,
hecho en **PHP + MySQL** y empaquetado con **Docker**. Permite crear, listar,
editar y eliminar productos, con login, registro y recuperación de contraseña
por pregunta de seguridad.

Proyecto de la materia **Desarrollo de Aplicaciones Multiplataforma**.

> 📖 **Guía de instalación paso a paso** (explicada pieza por pieza, con
> instrucciones por **Windows, macOS y Linux**): abrí el archivo
> **`docs/guia-instalacion.html`** directamente en tu navegador (doble clic).
> No necesita que el proyecto esté corriendo — justamente te guía para montarlo.

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
| Base de datos | MySQL 8 |
| Acceso a datos | PDO (prepared statements) |
| Estilos | CSS propio + Bootstrap 5 (local) |
| Entorno | Docker |
| Administración BD | phpMyAdmin |

---

## 🚀 Puesta en marcha (resumen)

**Requisitos:** **Docker Desktop** y **Git** instalados.

> 📖 Si nunca lo hiciste, seguí la **guía explicada paso a paso**:
> abrí **`docs/guia-instalacion.html`** en el navegador (incluye la instalación
> de Docker y Git según tu sistema operativo).
>
> 💻 Los comandos van en una **terminal**: **Git Bash** en Windows; **Terminal**
> en macOS/Linux. Escribí cada línea y presioná **Enter**.
>
> 🔑 **La contraseña de la base la elegís vos** (inventás una). Va en DOS lugares que deben coincidir: `secrets.php` (paso 2) y el `docker run` de MySQL (paso 4). Se clona PRIMERO porque la app se construye desde los archivos del repo.

```bash
# 1. Descargar el proyecto
git clone https://github.com/ra3gar/tarea_crud_php.git
cd tarea_crud_php

# 2. Crear secrets.php y poner TU contraseña (editá la línea $DB_PASS)
cp secrets.example.php secrets.php

# 3. Crear la red de Docker (para que los contenedores se vean entre sí)
docker network create red_proyectos

# 4. Levantar MySQL  (usá la MISMA clave que pusiste en secrets.php)
docker run -d --name mysql_global --network red_proyectos \
  -e MYSQL_ROOT_PASSWORD='mi_clave_local' -p 3306:3306 mysql:8.4

# 5. Levantar phpMyAdmin (administrar la base en localhost:8080)
docker run -d --name pma_global --network red_proyectos \
  -e PMA_HOST=mysql_global -p 8080:80 phpmyadmin

# 6. Construir la imagen de la app (ojo con el punto final)
docker build -t tarea_crud_php:8.5 .

# 7. Levantar la app (quedará en localhost:8001)
docker run -d --name tarea_crud_web --network red_proyectos \
  -p 8001:80 -v "$(pwd):/var/www/html" tarea_crud_php:8.5

# 8. Importar la base de datos (te pedirá la contraseña de MySQL)
docker exec -i mysql_global mysql -u root -p < database/schema.sql

# 9. Abrir http://localhost:8001  ->  "Crear cuenta" e iniciar sesión
```

> **Atajo opcional:** el proyecto incluye un `docker-compose.yml`. Una vez que
> entendés los pasos, `docker compose up -d --build` (con un `.env` que tenga
> `DB_PASS`) hace los pasos 3 a 8 de una sola vez.

### 🛠️ Comandos útiles

```bash
docker ps                                              # ver contenedores corriendo
docker stop tarea_crud_web pma_global mysql_global     # apagar (no borra nada)
docker start mysql_global pma_global tarea_crud_web    # encender
docker rm -f mysql_global                              # eliminar para recrear
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
├── secrets.example.php    Plantilla de credenciales (creás tu secrets.php)
├── database/schema.sql    Crea base, tablas y datos de prueba
├── includes/              Encabezado y pie comunes
├── assets/                Estilos, Bootstrap y fuentes (locales)
├── docs/                  Guía de instalación (guia-instalacion.html)
├── Dockerfile             Imagen PHP con drivers de MySQL
└── docker-compose.yml     Atajo opcional para levantar todo
```

## 🔐 Nota de seguridad

Las credenciales **no se versionan**: viven en `secrets.php` (o `.env`),
ignorados por git. Se incluye `secrets.example.php` como plantilla.

---

## 👥 Integrantes

- (Nombre y apellido)
- (Nombre y apellido)
- (Nombre y apellido)

> _Materia: Desarrollo de Aplicaciones Multiplataforma._
