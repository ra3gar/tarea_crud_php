---
name: memoria
description: Decisiones arquitectónicas y el porqué detrás de cada una. Más reciente arriba.
---

# Memoria de Decisiones

<!-- Último ID usado: DEC-005 -->

## Índice de decisiones
- DEC-001 — Contenedor e imagen PHP dedicados para la tarea
- DEC-002 — Reusar mysql_global con base propia
- DEC-003 — Seguridad: PDO prepared statements y hashing de secretos
- DEC-004 — Credenciales fuera del repositorio
- DEC-005 — Tema propio y assets locales (npm)

<!-- NUEVAS DECISIONES VAN AQUÍ, ARRIBA DE TODO -->

## DEC-005 | 2026-10-31 | Tema propio y assets locales (npm)
- **Contexto:** el catedrático pidió una plantilla; se evaluó bajar una de terceros.
- **Decisión:** tema CSS propio (camino B) + Bootstrap y fuentes servidos localmente desde /assets, traídos por npm.
- **Por qué:** para 8.5+Apache las plantillas de terceros van por detrás; el tema propio da control total y, con assets locales, la app funciona sin internet.

## DEC-004 | 2026-10-31 | Credenciales fuera del repositorio
- **Contexto:** el proyecto se sube a GitHub; la contraseña de MySQL no debe viajar en el repo.
- **Decisión:** las credenciales viven en secrets.php (ignorado por git); se versiona secrets.example.php como plantilla.
- **Por qué:** evita filtrar la contraseña en un repo público; cada integrante pone la suya.

## DEC-003 | 2026-10-31 | Seguridad: PDO prepared statements y hashing de secretos
- **Contexto:** la tarea maneja datos de formularios y login; el catedrático mencionó inyección SQL.
- **Decisión:** acceso a datos siempre con PDO + prepared statements; contraseñas y respuestas con password_hash/password_verify; salida escapada con htmlspecialchars.
- **Por qué:** previene inyección SQL y XSS, y protege credenciales aunque se filtre la base.

## DEC-002 | 2026-10-31 | Reusar mysql_global con base propia
- **Contexto:** ya existía un servidor MySQL en contenedor (mysql_global) con otras bases.
- **Decisión:** reusar ese servidor creando la base `tarea_crud_db`, en vez de levantar un MySQL dedicado.
- **Por qué:** un mismo servidor aloja bases independientes; más simple y no toca otros proyectos.

## DEC-001 | 2026-10-31 | Contenedor e imagen PHP dedicados para la tarea
- **Contexto:** existía un php_global compartido que ya servía otro proyecto.
- **Decisión:** imagen propia (Dockerfile FROM php:8.5-apache + docker-php-ext-install) y contenedor dedicado tarea_crud_web (puerto 8001).
- **Por qué:** aísla la tarea sin tocar php_global; la imagen oficial no trae drivers MySQL, por eso se agregan en el Dockerfile.
