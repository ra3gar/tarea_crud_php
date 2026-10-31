---
name: errores
description: Registro de bugs resueltos con causa raíz y solución. Más reciente arriba.
---

# Registro de Errores

<!-- Último ID usado: ERR-001 -->

## Índice de errores
- ERR-001 — docker build falla: "requires 1 argument"

<!-- NUEVOS ERRORES VAN AQUÍ, ARRIBA DE TODO -->

## ERR-001 | 2026-10-31 | docker build falla: "requires 1 argument"
- **Contexto:** al construir la imagen con `docker build -t tarea_crud_php:8.5` sin el punto final.
- **Causa raíz:** faltaba el contexto de build (`.`), argumento obligatorio que indica desde dónde construir.
- **Solución:** agregar el punto: `docker build -t tarea_crud_php:8.5 .`
- **Commit:** pendiente
