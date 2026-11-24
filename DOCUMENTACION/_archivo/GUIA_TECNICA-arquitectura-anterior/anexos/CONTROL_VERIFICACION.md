# Control de verificacion — Guia tecnica Tarea_CRUD_PHP

Anexo de la *Guia tecnica de arquitectura, estructura y codigo fuente* (v1.0, 2026-11-16).

Registro de las comprobaciones hechas sobre el documento antes de entregarlo, y de lo que
**no** pudo verificarse.

## 1. Verificacion de integridad de los listados de codigo

**Metodo.** Se extrajo todo el texto del `.docx` en orden de documento, leyendo directamente
`word/document.xml` (y no un PDF convertido, cuya extraccion reordena el texto en algunas
paginas). Para cada archivo se comprobo que su contenido completo, eliminados todos los
espacios en blanco, aparece como **subcadena contigua** del documento. Es una comprobacion
caracter a caracter: detecta cualquier linea omitida, truncada o alterada.

**Resultado: 48 de 48 listados integros.**

| # | Archivo | Lineas | Bytes | SHA-256 original | SHA-256 publicado | Integro |
|---|---|---|---|---|---|---|
| 1 | `public/index.php` | 25 | 1056 | `f03ff3b0262d9971` | `f03ff3b0262d9971` | SI |
| 2 | `public/.htaccess` | 23 | 1059 | `969a2995f50d9209` | `969a2995f50d9209` | SI |
| 3 | `routes/web.php` | 46 | 3236 | `01bebfa4589a9639` | `01bebfa4589a9639` | SI |
| 4 | `app/Core/autoload.php` | 37 | 1593 | `3b41617f5921d0df` | `3b41617f5921d0df` | SI |
| 5 | `app/Core/helpers.php` | 55 | 1942 | `27c6998a5c9c9a11` | `27c6998a5c9c9a11` | SI |
| 6 | `app/Core/Router.php` | 69 | 2805 | `8994f0bbf5ca532c` | `8994f0bbf5ca532c` | SI |
| 7 | `app/Core/Controller.php` | 43 | 1519 | `c7108f45aa1f0d45` | `c7108f45aa1f0d45` | SI |
| 8 | `app/Core/Database.php` | 51 | 2014 | `bf8554f19bac7669` | `bf8554f19bac7669` | SI |
| 9 | `app/Middleware/Auth.php` | 30 | 939 | `5f93c164fbc664c9` | `5f93c164fbc664c9` | SI |
| 10 | `app/Middleware/Admin.php` | 31 | 1120 | `a2633ed81a6c40bb` | `a2633ed81a6c40bb` | SI |
| 11 | `app/Controllers/ProductoController.php` | 182 | 6508 | `8f8e087566c482d2` | `8f8e087566c482d2` | SI |
| 12 | `app/Controllers/CategoriaController.php` | 60 | 2135 | `ac9086c48cdbd044` | `ac9086c48cdbd044` | SI |
| 13 | `app/Controllers/AuthController.php` | 182 | 7737 | `c1e965421063a0ec` | `c1e965421063a0ec` | SI |
| 14 | `app/Controllers/UsuarioController.php` | 227 | 8991 | `0073d952f34f5acc` | `0073d952f34f5acc` | SI |
| 15 | `app/Models/Producto.php` | 214 | 8488 | `4d18cda503c9d096` | `4d18cda503c9d096` | SI |
| 16 | `app/Models/Categoria.php` | 96 | 2949 | `01764a14d3cd7d5e` | `01764a14d3cd7d5e` | SI |
| 17 | `app/Models/Usuario.php` | 186 | 6591 | `01ce8a2a1e860293` | `01ce8a2a1e860293` | SI |
| 18 | `app/Views/layouts/header.php` | 68 | 4098 | `0ce7cffaf907a1c5` | `0ce7cffaf907a1c5` | SI |
| 19 | `app/Views/layouts/footer.php` | 12 | 657 | `76915fd6f411ebf8` | `76915fd6f411ebf8` | SI |
| 20 | `app/Views/productos/index.php` | 170 | 7747 | `d20214b6f5a6d0f8` | `d20214b6f5a6d0f8` | SI |
| 21 | `app/Views/productos/_campos.php` | 41 | 1783 | `b47361c2680ec8a1` | `b47361c2680ec8a1` | SI |
| 22 | `app/Views/productos/crear.php` | 24 | 1069 | `2193191ff99ecd45` | `2193191ff99ecd45` | SI |
| 23 | `app/Views/productos/editar.php` | 24 | 1119 | `be8f191e6155a60f` | `be8f191e6155a60f` | SI |
| 24 | `app/Views/productos/eliminar.php` | 22 | 1076 | `62424f984746b45b` | `62424f984746b45b` | SI |
| 25 | `app/Views/categorias/index.php` | 42 | 1490 | `d33c4d9ef1df0c9c` | `d33c4d9ef1df0c9c` | SI |
| 26 | `app/Views/auth/login.php` | 27 | 1500 | `6416e96503373be5` | `6416e96503373be5` | SI |
| 27 | `app/Views/auth/registro.php` | 43 | 2731 | `08b5460c3669ba86` | `08b5460c3669ba86` | SI |
| 28 | `app/Views/auth/recuperar.php` | 39 | 2557 | `af7bc5769200d6d7` | `af7bc5769200d6d7` | SI |
| 29 | `app/Views/usuarios/index.php` | 48 | 1953 | `7c7165b18e1ec11a` | `7c7165b18e1ec11a` | SI |
| 30 | `app/Views/usuarios/crear.php` | 43 | 2586 | `eb1b59c2177e86d3` | `eb1b59c2177e86d3` | SI |
| 31 | `app/Views/usuarios/editar.php` | 40 | 2411 | `80ec014da1bedab6` | `80ec014da1bedab6` | SI |
| 32 | `public/assets/css/estilo.css` | 208 | 11558 | `841694c3c9c97fe9` | `841694c3c9c97fe9` | SI |
| 33 | `config/config.php` | 22 | 1081 | `d93f4b111cd0be06` | `d93f4b111cd0be06` | SI |
| 34 | `.env.example` | 6 | 342 | `09c4052a8be8e311` | `09c4052a8be8e311` | SI |
| 35 | `secrets.example.php` | 10 | 539 | `d3224a988d377fd8` | `d3224a988d377fd8` | SI |
| 36 | `.gitignore` | 9 | 195 | `94f80ca04755f26e` | `94f80ca04755f26e` | SI |
| 37 | `CLAUDE.md` | 26 | 1064 | `da377962857089f4` | `da377962857089f4` | SI |
| 38 | `.claude/settings.json` | 8 | 115 | `a77ca9aaf5a75438` | `a77ca9aaf5a75438` | SI |
| 39 | `database/schema.sql` | 101 | 5802 | `5eeabeba3d4f89ad` | `4abb82222dff3ab2` | SI |
| 40 | `database/migracion-01-usuarios-roles.sql` | 30 | 1673 | `666651911fb666b4` | `8036c6cb7278b8c9` | SI |
| 41 | `database/migracion-02-fk-categoria.sql` | 23 | 1124 | `76fa549e5afa405a` | `76fa549e5afa405a` | SI |
| 42 | `database/seed-categorias.sql` | 5 | 261 | `b1182eebc8d2e128` | `b1182eebc8d2e128` | SI |
| 43 | `Dockerfile` | 14 | 540 | `17ae14e000394bad` | `17ae14e000394bad` | SI |
| 44 | `docker-compose.yml` | 123 | 5441 | `7605002d067644cb` | `7605002d067644cb` | SI |
| 45 | `docker/apache.conf` | 10 | 406 | `f8582b54d4ec27e5` | `f8582b54d4ec27e5` | SI |
| 46 | `apache-deny.conf` | 10 | 312 | `b9d7e5194f8b1204` | `b9d7e5194f8b1204` | SI |
| 47 | `includes/header.php` | 37 | 1637 | `00ad67d3e97bdef3` | `00ad67d3e97bdef3` | SI |
| 48 | `includes/footer.php` | 8 | 377 | `e4f7244bb928b2b8` | `e4f7244bb928b2b8` | SI |

Los SHA-256 (primeros 16 caracteres) difieren **solo** en los dos archivos con valores
redactados, lo que se detalla en el apartado siguiente. En los 46 restantes coinciden, es decir,
el texto publicado es identico al del repositorio.

## 2. Valores omitidos por seguridad

El encargo pide incluir el codigo integro **y** no exponer credenciales. Donde ambas cosas
chocaban, prevalecio la segunda. Se omitieron estos valores, y solo estos:

| Archivo | Que se omitio | Sustituido por |
|---|---|---|
| `database/schema.sql` | 2 hash(es) bcrypt del administrador sembrado | `<<HASH BCRYPT OMITIDO EN ESTE DOCUMENTO>>` |
| `database/schema.sql` | 2 valor(es) en claro escritos en comentarios (contrasena y respuesta de seguridad) | `<<OMITIDA>>` |
| `database/migracion-01-usuarios-roles.sql` | 2 hash(es) bcrypt del administrador sembrado | `<<HASH BCRYPT OMITIDO EN ESTE DOCUMENTO>>` |
| `database/migracion-01-usuarios-roles.sql` | 1 valor(es) en claro escritos en comentarios (contrasena y respuesta de seguridad) | `<<OMITIDA>>` |

Ninguna otra linea de ningun otro archivo fue alterada. Las credenciales de acceso al
sistema estan en el manual oficial del proyecto, `docs/guia-instalacion.html`.

**Comprobacion adicional:** se verifico que el documento final no contiene ningun hash bcrypt
(`$2y$...`) ni la contrasena en claro del administrador sembrado.

| Comprobacion | Resultado |
|---|---|
| Hashes bcrypt presentes en el documento | **Ninguno** |
| Contrasena del admin sembrado en claro | **Ninguna** |
| Contenido de `.env` o `secrets.php` | **No leido en ningun momento** |

## 3. Comprobaciones de maquetacion

| Comprobacion | Metodo | Resultado |
|---|---|---|
| El `.docx` abre y mantiene el formato | Conversion a PDF con LibreOffice e inspeccion visual de las paginas | **Correcto** (144 paginas) |
| Paginas en blanco innecesarias | Extraccion de texto por pagina y deteccion de paginas sin contenido | **Ninguna** |
| Bloques de codigo cortados de forma ilegible | Ajuste automatico del cuerpo de letra a la linea mas larga de cada archivo; listados de hasta 38 lineas marcados para no partirse entre paginas | **Corregido** |
| Tablas cortadas | Encabezado de tabla marcado para repetirse al cruzar de pagina (`tblHeader`) | **Correcto** |
| Diagramas ilegibles por tamano | Los tres diagramas de secuencia y las dos matrices se colocaron en **paginas horizontales** | **Corregido** |
| Indice automatico | Campo `TOC` insertado; requiere que el lector lo actualice con F9 | **Presente** |
| Numeracion de paginas | Campos `PAGE` y `NUMPAGES` en el pie; portada sin encabezado ni pie | **Correcto** |
| Estilos editables en Word | Encabezados con los estilos nativos `Heading 1-4`; tablas y texto editables | **Correcto** |
| Imagenes incrustadas | 11 figuras incrustadas en el archivo, no enlazadas | **Correcto** |

## 4. Comprobaciones de exactitud tecnica

| Comprobacion | Como se verifico | Resultado |
|---|---|---|
| El arbol de directorios coincide con el repositorio | `find` sobre el repositorio, contrastado elemento por elemento | **Coincide** |
| Los nombres de archivo y las rutas existen | Las 48 rutas documentadas se leyeron del disco al generar el documento; una ruta inexistente habria abortado la generacion | **Todas existen** |
| El patron MVC esta correctamente identificado | Lectura de los `use`, `extends` y llamadas de cada archivo; los componentes que no son M, V ni C se documentan como tales | **Verificado** |
| Las relaciones entre archivos son reales | Cada arista del mapa corresponde a un `require`, `use`, `extends` o `new` leido en el codigo; las relaciones por `$_SESSION` se marcan como inferidas | **Verificado** |
| El diccionario de datos corresponde al esquema | Contrastado columna por columna contra `schema.sql` y las dos migraciones | **Coincide** |
| Codigo muerto | Busqueda de `includes/` en todos los `.php`, `.yml` y `.conf`: cero coincidencias | **Confirmado** |

## 5. Elementos que no pudieron verificarse

| Elemento | Motivo |
|---|---|
| Ejecucion real del sistema | El analisis se hizo sobre el codigo fuente; no se levantaron los contenedores ni se ejecutaron consultas contra una base real. |
| Comportamiento real de la clave foranea | Se describe segun lo declarado en `schema.sql`. El registro del proyecto documenta una prueba manual previa del alta con y sin categoria. |
| Contenido de `bootstrap.min.css` y de las fuentes `.woff2` | Dependencias externas y binarios; excluidos por el criterio del capitulo 3. |
| Valores reales de `.env` y `secrets.php` | Deliberadamente no leidos. |
| Apertura en Microsoft Word para Mac | La revision visual se hizo convirtiendo a PDF con LibreOffice. No se dispuso de Word para Mac. |
| Numeracion final del indice | Es un campo automatico: los numeros de pagina aparecen cuando el lector lo actualiza con F9. |

## 6. Hallazgos abiertos

La auditoria dejo nueve hallazgos sobre el codigo (capitulo 41.1 del documento) y tres
discrepancias entre el codigo y la documentacion previa del repositorio (capitulo 41.2).
Ninguno impide el funcionamiento del sistema. Los dos mas accionables:

1. **H-1** — eliminar la carpeta `includes/`, que es codigo muerto.
2. **H-2** — unificar la constante `PREGUNTAS`, hoy duplicada en dos controladores.

