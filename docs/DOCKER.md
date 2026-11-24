# Docker

El entorno **no se modifico** en la reestructuracion: siguen los mismos tres
servicios, puertos, volumen y red.

> **Diagrama**: `DOCUMENTACION/recursos/diagramas/05-docker.png` muestra los
> tres servicios, la red, el volumen y los puertos publicados.

## 1. Los tres servicios

| Servicio | Contenedor | Imagen | Puerto host:contenedor |
|---|---|---|---|
| `php` | `tarea_crud_php` | se **construye** desde el `Dockerfile` | `8001:80` |
| `mysql` | `tarea_crud_mysql` | `mysql:8.4` | `3307:3306` |
| `phpmyadmin` | `tarea_crud_phpmyadmin` | `phpmyadmin:latest` | `8081:80` |

Por que el de PHP se construye y los otros no: la imagen oficial de PHP no trae
los drivers de MySQL. El `Dockerfile` agrega `pdo_mysql` y `mysqli`, habilita
`mod_rewrite` y copia las dos configuraciones de Apache.

Por que puertos raros: **3307** en vez de 3306 y **8081** en vez de 8080, para
no chocar con otro MySQL o otro phpMyAdmin que ya este corriendo en la maquina.

## 2. Volumen y red

| Recurso | Nombre | Para que |
|---|---|---|
| Volumen | `tarea_crud_mysql_datos` | El disco de la base. Sobrevive a borrar contenedores |
| Red | `tarea_crud_red` | Red privada solo de este proyecto |
| Bind mount | `.:/var/www/html` | Editar un `.php` se ve al recargar, sin reconstruir |

Todo lleva el prefijo `tarea_crud_`: el proyecto se levanta y se apaga sin tocar
nada mas de la maquina.

## 3. Variables de entorno

| Variable | Valor | Donde |
|---|---|---|
| `DB_HOST` | `tarea_crud_mysql` | servicio `php` |
| `DB_NAME` | `tarea_crud_db` | servicio `php` |
| `DB_USER` | `root` | servicio `php` |
| `DB_PASS` | sale de `.env` | `php` y `mysql` |
| `MYSQL_DATABASE` | `tarea_crud_db` | servicio `mysql` |
| `PMA_HOST` | `tarea_crud_mysql` | servicio `phpmyadmin` |

`DB_PASS` se define una vez en `.env` (copiado de `.env.example`) y lo usan la
base y la aplicacion. `.env` **no se versiona**.

**Por que `DB_HOST` es `tarea_crud_mysql` y no `localhost`**: dentro de una red
de Docker el nombre del contenedor funciona como nombre de host. Desde el
contenedor de PHP, `localhost` seria el propio contenedor de PHP, donde no hay
ninguna base escuchando.

## 4. Comandos

```bash
cp .env.example .env            # una sola vez: elegi tu clave local
docker compose up -d --build    # levantar (construye la imagen de PHP)
docker compose down             # apagar CONSERVANDO los datos
docker compose down -v          # apagar BORRANDO la base y sus datos
docker compose up -d --build php  # reconstruir solo PHP
docker compose ps               # estado de los tres servicios
```

> `docker compose down -v` borra el volumen y con el **todos los productos,
> categorias y usuarios**. Haz respaldo antes (ver `BASE_DATOS.md`).

## 5. Registros de ejecucion

```bash
docker compose logs -f           # todos los servicios
docker compose logs -f php       # solo Apache + PHP (errores de PHP aparecen aca)
docker compose logs -f mysql
docker exec -it tarea_crud_php bash    # entrar al contenedor
```

Los errores de conexion a la base se escriben con `error_log()` y salen en los
registros de `php`, no en la pantalla del usuario.

## 6. Accesos

| Que | Donde | Credenciales |
|---|---|---|
| La aplicacion | http://localhost:8001 | las del sistema (ver manual) |
| phpMyAdmin | http://localhost:8081 | usuario `root`, clave = `DB_PASS` de `.env` |
| MySQL externo (DBeaver) | `localhost:3307` | usuario `root`, clave = `DB_PASS` |

## 7. Orden de arranque

`php` declara `depends_on: mysql: condition: service_healthy`, y `mysql` tiene
un `healthcheck` con `mysqladmin ping`. Sin eso, el primer arranque mostraria un
error de conexion hasta recargar a mano.
