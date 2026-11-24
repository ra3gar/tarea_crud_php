# Partimos de la imagen oficial de PHP 8.5 con Apache.
FROM php:8.5-apache

# -----------------------------------------------------------------------
# Extensiones del sistema que PhpSpreadsheet y Dompdf necesitan.
#   libzip-dev, libpng-dev, libjpeg-dev, libfreetype6-dev  → imágenes/ZIP
#   libxml2-dev                                             → XML (Dompdf)
#   libgmp-dev                                              → GMP (PhpSpreadsheet)
# -----------------------------------------------------------------------
RUN apt-get update && apt-get install -y \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libxml2-dev \
        libgmp-dev \
        unzip \
        git \
    && rm -rf /var/lib/apt/lists/*

# Configurar GD con soporte JPEG y FreeType (necesario para PDFs con imágenes)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Drivers de MySQL y extensiones adicionales para las librerías de exportación
RUN docker-php-ext-install \
        pdo_mysql \
        mysqli \
        zip \
        gd \
        gmp \
        xml \
        dom \
        mbstring \
        intl \
        exif

# Habilita mod_rewrite (para las URLs limpias del .htaccess).
RUN a2enmod rewrite

# Instalar Composer desde su imagen oficial (no contamina la imagen con extras)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache sirve public/ como raiz web (ver docker/apache.conf).
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Seguridad extra: niega servir archivos sensibles aunque quedaran expuestos.
COPY apache-deny.conf /etc/apache2/conf-enabled/zz-deny-sensitive.conf

# Copiar composer.json y composer.lock (si existe) e instalar dependencias.
# --no-interaction  → no preguntas durante el build
# --no-dev          → no instalar paquetes de desarrollo (phpunit, etc.)
# --optimize-autoloader → autoloader más rápido para producción/clase
COPY composer.json ./
RUN composer install --no-interaction --no-dev --optimize-autoloader
