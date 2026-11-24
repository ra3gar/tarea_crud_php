# Instalacion y ejecucion

> El manual de uso del sistema, con las credenciales de acceso, es
> `docs/guia-instalacion.html`. Este archivo es la version resumida en
> Markdown de la puesta en marcha.

## 1. Requisitos

Solo **Docker Desktop** (o Docker Engine + Docker Compose) y **git**.

No hace falta instalar PHP, Apache ni MySQL en la maquina: los tres corren en
contenedores.

## 2. Preparacion

```bash
git clone <url-del-repositorio>
cd Tarea_CRUD_PHP
cp .env.example .env
```

Edita `.env` y pone tu propia clave local:

```
DB_PASS=una_clave_local_cualquiera
```

Esa clave la usan la base (usuario `root`) y la aplicacion. `.env` no se sube a
git.

> Si cambias `DB_PASS` **despues** de haber levantado el proyecto, hay que
> borrar el volumen para que tome la nueva: `docker compose down -v` (esto borra
> los datos).

## 3. Instalacion y ejecucion

```bash
docker compose up -d --build
```

La primera vez construye la imagen de PHP y crea la base ejecutando
`database/schema.sql`, que deja las tablas, los datos de prueba y el
administrador inicial. Tarda un poco mas que los arranques siguientes.

Comprobar que los tres servicios estan arriba:

```bash
docker compose ps
```

## 4. Acceso

| Que | Donde |
|---|---|
| La aplicacion | http://localhost:8001 |
| phpMyAdmin | http://localhost:8081 (usuario `root`, clave = `DB_PASS`) |
| MySQL desde un gestor externo | `localhost:3307` |

Las credenciales del administrador inicial estan en
`docs/guia-instalacion.html`.

## 5. Detener

```bash
docker compose down        # apaga y CONSERVA los datos
docker compose down -v     # apaga y BORRA la base
```

## 6. Instalacion sin Docker (alternativa)

Si en lugar de Docker se usa un servidor propio (XAMPP, Laragon, un hosting):

1. Apunta la raiz web del servidor a la carpeta **`public/`**, no a la raiz del
   proyecto. Es lo que mantiene `app/` y `config/` fuera del alcance del
   navegador.
2. Crea la base ejecutando `database/schema.sql` (phpMyAdmin: pestaña
   Importar).
3. Copia `secrets.example.php` como `secrets.php` en la raiz y completa host,
   base, usuario y clave. `config.php` lo usa cuando no hay variables de
   entorno.
4. PHP 8.1 o superior con la extension `pdo_mysql`.

## 7. Problemas frecuentes

| Sintoma | Causa probable | Solucion |
|---|---|---|
| "falta DB_PASS" al levantar | No existe `.env` | `cp .env.example .env` y poner una clave |
| Error de conexion a la base en el primer arranque | MySQL todavia inicializandose | Esperar unos segundos y recargar. El `healthcheck` deberia evitarlo |
| El puerto 8001 esta ocupado | Otro servicio lo usa | Cambiar `"8001:80"` en `docker-compose.yml` |
| El puerto 3307 esta ocupado | Otro MySQL | Cambiar `"3307:3306"` |
| Cambie la clave y no entra | El volumen conserva la clave vieja | `docker compose down -v` y levantar de nuevo (borra los datos) |
| Pagina en blanco | Error de PHP | `docker compose logs -f php` |
| "La solicitud no pudo verificarse" | La sesion caduco con el formulario abierto | Recargar la pagina y reenviar |
| Los estilos no se actualizan | Cache del navegador | Recarga forzada (Ctrl/Cmd + Shift + R) |
