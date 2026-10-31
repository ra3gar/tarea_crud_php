---
name: arquitectura
description: Endpoints, vistas, componentes y reglas de negocio del proyecto.
---

# Arquitectura

## Endpoints
App PHP servida por Apache; cada archivo .php es una página accesible por URL.

## Vistas / Componentes
- index.php — listar productos (Read). Página principal; muestra métricas y badges de stock.
- crear.php — alta de producto (Create).
- editar.php — modificación de producto (Update).
- eliminar.php — baja de producto con confirmación (Delete).
- login.php / logout.php — inicio y cierre de sesión.
- registro.php — alta de usuario (contraseña y respuesta de seguridad hasheadas).
- recuperar.php — restablecer contraseña por pregunta de seguridad (2 pasos).
- config.php — conexión PDO (lee credenciales de secrets.php).
- auth.php — guardia: exige sesión iniciada en páginas privadas.
- includes/header.php, includes/footer.php — layout común.
- assets/ — tema (estilo.css), Bootstrap y fuentes locales.

## Reglas de negocio
- Las páginas del CRUD son privadas: requieren sesión iniciada (auth.php).
- Todo acceso a la base de datos se hace con PDO + prepared statements.
- Contraseñas y respuestas de seguridad se guardan hasheadas; la salida se escapa con htmlspecialchars.
- Estado de stock: 0 = sin stock, menor a 10 = bajo (se refleja con color en la lista).
