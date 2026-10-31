---
name: contexto-minimal
description: Stack, tecnologías e invariantes VIGENTES del proyecto. Se lee completo en cada arranque. Lo superado migra a MEMORIA.md, no se borra.
---

# Contexto Mínimo — Tarea_CRUD_PHP (Inventario de productos)

## Stack
- Lenguaje: PHP 8.5 (8.5.10)
- Framework: ninguno (PHP vanilla)
- Build: ninguno (Apache sirve PHP directo; sin bundler)
- Base de datos: MySQL (contenedor mysql:latest) — base `tarea_crud_db`

## Tecnologías estructurales
- PDO — acceso a datos con prepared statements (anti inyección SQL)
- Bootstrap 5.3.3 — base de estilos (local, en /assets)
- Fuentes Inter y Space Grotesk (locales, en /assets/fonts)
- Docker — entorno; contenedor dedicado `tarea_crud_web` desde imagen propia `tarea_crud_php:8.5` (= php:8.5-apache + drivers pdo_mysql/mysqli)

## Restricciones de versión
- PHP 8.5 (imagen base php:8.5-apache)

## Decidido NO usar
- Sin framework PHP (PHP vanilla, fines académicos)
- Sin ORM (consultas directas con PDO)
- Sin CDN (Bootstrap y fuentes servidos localmente)

## Estructura de carpetas
- `/`                  -> páginas PHP (raíz web = /var/www/html del contenedor)
- `/database`          -> schema.sql (crea base + tablas + datos de prueba)
- `/includes`          -> layout común (header.php, footer.php)
- `/assets`            -> tema (css/estilo.css), Bootstrap y fuentes locales
- `/DOCUMENTACION_MD`  -> memoria/contexto del proyecto
- `Dockerfile`         -> receta de la imagen PHP con drivers MySQL
- `config.php`         -> conexión PDO a MySQL (lee secrets.php)
- `secrets.php`        -> credenciales reales (NO se versiona; ver .gitignore)
- `auth.php`           -> guardia de sesión para páginas privadas

## Invariantes — no romper nunca
1. Acceso a MySQL SIEMPRE con PDO + prepared statements. Nunca concatenar variables en el SQL (previene inyección SQL).
2. Contraseñas y respuestas de seguridad SIEMPRE hasheadas con password_hash()/password_verify(). Nunca en texto plano.
3. Host de la base de datos = `mysql_global` (nombre del contenedor en la red `red_proyectos`), NO `localhost`.
4. Codificación utf8mb4 en base y tablas (tildes/ñ).
5. Credenciales NUNCA en el repo: viven en secrets.php (ignorado por git).

## Comandos frecuentes
- App: http://localhost:8001
- phpMyAdmin: http://localhost:8080 (usuario root; la contraseña vive en el entorno, NO en el repo)
- Construir imagen: `docker build -t tarea_crud_php:8.5 .`
- Cargar/resetear BD: `docker exec -i mysql_global mysql -u root -p < database/schema.sql`
- Tests: (por definir)
