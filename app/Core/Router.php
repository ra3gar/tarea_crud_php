<?php
namespace App\Core;

// =====================================================================
//  app/Core/Router.php — El que decide, segun la direccion pedida, que
//  controlador responde.
//
//  Ubicacion en la arquitectura por modulos:
//    app/Core/        <- componentes transversales del framework casero
//    app/Modules/     <- cada modulo tiene su Controlador, Modelo y Vistas
//    app/Helpers/     <- funciones de apoyo compartidas
//
//  Que problema resuelve: el navegador pide /productos/5/editar. Alguien
//  tiene que traducir esa direccion a "ejecutar el metodo editar() del
//  ProductoController pasandole el 5". Eso es todo lo que hace este archivo.
//
//  Por que es una clase y no funciones sueltas: porque SI necesita guardar
//  estado. La tabla de rutas se llena con llamadas a agregar() y despues
//  se consulta en despachar(). Ese arreglo compartido entre los dos metodos
//  es exactamente lo que una clase resuelve bien.
//
//  Con quien se relaciona:
//    - routes/web.php              le carga la tabla de rutas
//    - public/index.php            lo crea y le pide despachar la peticion
//    - app/Helpers/funciones.php   le presta proteger_sesion() y proteger_admin()
// =====================================================================

/**
 * Enrutador principal del sistema.
 *
 * Guarda una tabla de rutas y, por cada peticion HTTP recibida,
 * elige al controlador responsable y verifica los permisos necesarios
 * antes de ejecutarlo.
 */
class Router
{
    /**
     * Tabla interna de rutas registradas.
     *
     * Cada elemento es un arreglo asociativo con las claves:
     *   'metodo'  -> verbo HTTP (GET o POST)
     *   'patron'  -> forma de la URL, puede incluir {id} como parte variable
     *   'accion'  -> [NombreClase::class, 'nombreMetodo'] a ejecutar
     *   'permiso' -> nivel de acceso requerido (null, 'sesion' o 'admin')
     *
     * @var array<int, array{metodo:string, patron:string, accion:array, permiso:string|null}>
     */
    private array $rutas = [];

    /**
     * Registra una ruta en la tabla interna.
     *
     * Solo guarda la informacion; no ejecuta ningun controlador todavia.
     * Se llama desde routes/web.php para definir todas las rutas del sistema.
     *
     * @param string      $metodo  Verbo HTTP esperado: "GET" o "POST".
     * @param string      $patron  Forma de la direccion. Puede incluir {id}
     *                             para segmentos variables numericos.
     *                             Ejemplos: '/productos', '/productos/{id}/editar'
     * @param array       $accion  Par [ControladorClass::class, 'nombreMetodo'].
     * @param string|null $permiso Nivel de acceso requerido:
     *                             null     -> cualquiera puede acceder (login, registro)
     *                             'sesion' -> requiere estar autenticado
     *                             'admin'  -> requiere sesion activa Y rol de administrador
     * @return void
     */
    public function agregar(string $metodo, string $patron, array $accion, ?string $permiso = null): void
    {
        $this->rutas[] = [
            'metodo'  => $metodo,
            'patron'  => $patron,
            'accion'  => $accion,
            'permiso' => $permiso,
        ];
    }

    /**
     * Busca la ruta coincidente, verifica permisos y ejecuta el controlador.
     *
     * Pasos internos:
     *   1. Parte la URL en segmentos ['productos', '5', 'editar']
     *   2. Recorre la tabla hasta encontrar un patron que coincida
     *   3. Verifica permisos segun el nivel configurado en la ruta
     *   4. Instancia el controlador y llama al metodo, pasando el {id} si lo hay
     *   5. Si ningun patron coincide, responde con HTTP 404
     *
     * @param string $metodo Verbo HTTP de la peticion actual ("GET" o "POST").
     * @param string $ruta   URL pedida por el navegador (ej. "/productos/5/editar").
     * @return void          La respuesta la imprime el controlador directamente.
     */
    public function despachar(string $metodo, string $ruta): void
    {
        // Convertir "/productos/5/editar" en ['productos', '5', 'editar']
        $partesRuta = $this->partes($ruta);

        foreach ($this->rutas as $r) {
            // Descarte rapido: si el verbo HTTP no coincide, ni comparar la URL
            if ($r['metodo'] !== $metodo) {
                continue;
            }

            // Comparar patron con la URL real; extraer {id} si existe
            $id = $this->coincide($this->partes($r['patron']), $partesRuta);

            // coincide() retorna false cuando la ruta NO corresponde a este patron
            if ($id === false) {
                continue;
            }

            // --- Verificacion de permisos ANTES de ejecutar el controlador ---
            // El orden importa: primero verificar sesion, luego el rol de admin
            if ($r['permiso'] === 'sesion' || $r['permiso'] === 'admin') {
                proteger_sesion(); // Redirige al login si no hay sesion activa
            }
            if ($r['permiso'] === 'admin') {
                proteger_admin();  // Redirige si el usuario no tiene rol 'admin'
            }

            // --- Instanciar el controlador y ejecutar el metodo ---
            // $clase es un string con el nombre completo de la clase
            // new $clase() permite crear objetos con nombres conocidos en tiempo de ejecucion
            [$clase, $metodoCtrl] = $r['accion'];
            $controlador = new $clase();

            // Si el patron tenia {id}, se lo pasamos al metodo; si no, se llama sin argumentos
            if ($id === null) {
                $controlador->$metodoCtrl();
            } else {
                $controlador->$metodoCtrl($id);
            }
            return; // Ruta encontrada y ejecutada; detenemos la busqueda
        }

        // Ninguna ruta coincidio con la peticion actual
        http_response_code(404);
        echo 'Pagina no encontrada (404).';
    }

    /**
     * Divide una URL o patron en sus segmentos, descartando los vacios.
     *
     * Ejemplos:
     *   '/productos/{id}/editar' -> ['productos', '{id}', 'editar']
     *   '/'                      -> []  (portada, sin segmentos)
     *   '/productos'             -> ['productos']
     *
     * @param string $ruta URL o patron a dividir.
     * @return array<int, string> Segmentos resultantes, sin vacios.
     */
    private function partes(string $ruta): array
    {
        return array_values(array_filter(explode('/', $ruta), fn($p) => $p !== ''));
    }

    /**
     * Compara un patron con los segmentos de una URL pedida.
     *
     * Recorre los segmentos uno a uno:
     *   - Si el segmento del patron es '{id}', acepta cualquier numero entero
     *     y lo guarda como el identificador de recurso.
     *   - Si es texto fijo, debe coincidir exactamente con el segmento de la URL.
     *
     * Por que no usar expresiones regulares: comparar segmento a segmento es
     * mas legible y facil de mantener para las rutas simples de este sistema.
     *
     * @param array<int, string> $patron  Segmentos del patron registrado.
     * @param array<int, string> $ruta    Segmentos de la URL pedida.
     * @return int|null|false
     *   int   -> el {id} extraido si el patron lo incluia
     *   null  -> coincide perfectamente pero sin {id}
     *   false -> el patron NO coincide con la URL pedida
     */
    private function coincide(array $patron, array $ruta): int|null|false
    {
        // Distinta cantidad de segmentos: imposible que sea este patron
        if (count($patron) !== count($ruta)) {
            return false;
        }

        $id = null;

        foreach ($patron as $i => $pedazo) {
            if ($pedazo === '{id}') {
                // Solo se acepta un numero entero en la posicion del {id}.
                // Esto evita que /productos/crear se confunda con /productos/{id}
                // y valida el identificador antes de que llegue al modelo.
                if (!ctype_digit($ruta[$i])) {
                    return false;
                }
                $id = (int) $ruta[$i];
                continue;
            }

            // Segmento fijo: debe ser identico, letra por letra
            if ($pedazo !== $ruta[$i]) {
                return false;
            }
        }

        return $id; // null si no habia {id}, entero si lo habia
    }
}
