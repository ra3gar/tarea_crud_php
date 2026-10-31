---
name: convenciones
description: Reglas de formato del repositorio de contexto. Fuente única de verdad para las skills romina.
---

# Convenciones del Repositorio de Contexto

## Regla 0 — Preservación (prioritaria sobre todas las demás)
Ninguna skill `romina` puede borrar un archivo, eliminar una entrada ya escrita, ni modificar el texto de un registro previo. Todas esas acciones requieren solicitud explícita del usuario nombrando el archivo/registro.

## Regla 1 — Orden cronológico inverso
En SESSION.md, MEMORIA.md, ERRORES.md y GIT.md, la entrada más reciente va ARRIBA, inmediatamente después del frontmatter.

## Regla 2 — Crecimiento controlado SIN pérdida
Archivos de ESTADO (CONTEXTO-MINIMAL, ARQUITECTURA, DB-ESQUEMA): describen el presente; lo superado migra a MEMORIA.md como DEC-00X. Archivos de BITÁCORA (SESSION, MEMORIA, ERRORES, GIT): acumulativos; nunca se borra, se archiva por trimestre con autorización.

## Regla 3 — Lectura de arranque (por REGISTROS, no líneas)
Cantidades AJUSTABLES POR PROYECTO:
- `SESSION.md`  → últimas **3** entradas completas
- `MEMORIA.md`  → índice COMPLETO + últimas **3** decisiones completas
romina-inicia lee solo: CONTEXTO-MINIMAL (completo), SESSION (3), MEMORIA (índice + 3), PLANIFICACION (Sprint actual), GIT (Pendiente de commit).

## Regla 4 — IDs
Formato `ERR-001`, `DEC-001`. Correlativos, nunca reutilizados. Leer el archivo antes de asignar uno nuevo.

## Regla 5 — Formato de entradas
SESSION 6-8 líneas, MEMORIA 10-12, ERRORES 6-8. Plantillas en la skill romina-scaffold.

## Regla 6 — Sin suposiciones
Documentar solo lo codificado, debatido o aprobado. Dato desconocido = `(por definir)`.
