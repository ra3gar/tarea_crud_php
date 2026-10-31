# Partimos de TU imagen ya instalada: php:8.5-apache (no se descarga nada nuevo)
FROM php:8.5-apache

# "EL PARCHE": drivers de MySQL que la imagen oficial NO trae.
RUN docker-php-ext-install pdo_mysql mysqli

# Seguridad: Apache no debe servir como texto archivos sensibles (.env, secrets.php, etc.)
COPY apache-deny.conf /etc/apache2/conf-enabled/zz-deny-sensitive.conf
