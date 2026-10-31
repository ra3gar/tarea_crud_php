---
name: db-esquema
description: Entidades, relaciones y changelog estructural de la base de datos.
---

# Esquema de Base de Datos

## Entidades
- productos(id PK, nombre, descripcion, precio DECIMAL(10,2), stock, categoria, fecha_creacion)
- usuarios(id PK, usuario UNIQUE, password_hash, pregunta, respuesta_hash, fecha_creacion)

## Relaciones
- (ninguna por ahora; productos y usuarios son independientes)

## Changelog estructural
<!-- Migraciones, más reciente arriba. Debe coincidir con GIT.md -->
- 2026-10-31 — Creacion inicial: tablas productos y usuarios (database/schema.sql)
