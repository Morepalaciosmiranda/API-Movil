# Usa una imagen base oficial de PHP con Apache.
FROM php:8.1-apache

# Establece el directorio de trabajo en el contenedor.
WORKDIR /var/www/html

# Instala las dependencias necesarias
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    git

# Instala las extensiones necesarias de PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql zip

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia el código fuente de tu aplicación al contenedor.
COPY . .

# Instala las dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Instala y habilita el módulo MPM prefork y rewrite
RUN a2enmod mpm_prefork rewrite

# Expone el puerto en el que Apache escuchará.
EXPOSE 80

# Comando para iniciar Apache en primer plano.
CMD ["apache2-foreground"]