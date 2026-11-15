# Partimos de la imagen oficial de PHP 8.5 con Apache.
FROM php:8.5-apache

# Drivers de MySQL que la imagen no trae.
RUN docker-php-ext-install pdo_mysql mysqli

# Habilita mod_rewrite (para las URLs limpias opcionales del .htaccess).
RUN a2enmod rewrite

# Apache sirve public/ como raiz web (ver docker/apache.conf).
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Seguridad extra: niega servir archivos sensibles aunque quedaran expuestos.
COPY apache-deny.conf /etc/apache2/conf-enabled/zz-deny-sensitive.conf
