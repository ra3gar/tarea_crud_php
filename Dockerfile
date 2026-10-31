# Partimos de TU imagen ya instalada: php:8.5-apache (no se descarga nada nuevo)
FROM php:8.5-apache

# "EL PARCHE": agregamos los drivers de MySQL que la imagen oficial NO trae.
# docker-php-ext-install es el ayudante oficial que ya viene dentro de la imagen de PHP.
# Compila e instala estas extensiones contra el PHP instalado:
#   pdo_mysql -> para conectar con PDO (lo que usaremos, por seguridad / prepared statements)
#   mysqli    -> driver alternativo, lo dejamos instalado por si acaso
RUN docker-php-ext-install pdo_mysql mysqli