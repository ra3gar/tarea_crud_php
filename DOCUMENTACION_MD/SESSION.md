---
name: session
description: Bitácora ejecutiva. Qué se hizo y en qué punto quedó cada sesión. Más reciente arriba.
---

# Bitácora de Sesiones

<!-- NUEVAS ENTRADAS VAN AQUÍ, ARRIBA DE TODO -->

## 2026-10-31 — CRUD de inventario completo (Fases 0 a 8)
**Hecho:**
- Entorno Docker dedicado (imagen propia php:8.5 + drivers pdo_mysql/mysqli) y base `tarea_crud_db` (tablas productos, usuarios).
- CRUD completo en PHP/PDO con prepared statements (crear, listar, editar, eliminar).
- Autenticación: login, registro, logout, protección de páginas y recuperación por pregunta de seguridad (password_hash).
- Tema visual propio + assets locales (Bootstrap y fuentes vía npm, sin CDN).
**Estado:** app funcional y con estilo; corre en http://localhost:8001.
**Siguiente:** commit a git, portada con nombres del grupo, comprimir y subir al Aula Virtual (antes del martes).
