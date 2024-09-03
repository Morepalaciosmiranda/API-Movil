# Usa una imagen base oficial de PHP con Apache.
FROM php:8.1-apache

# Establece el directorio de trabajo en el contenedor.
WORKDIR /var/www/html

# Instala las dependencias necesarias
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    git \
    curl

# Instala las extensiones necesarias de PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql zip

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia el código fuente de tu aplicación al contenedor.
COPY . .

COPY ./uploads /var/www/html/uploads
# Instala TCPDF manualmente
RUN mkdir -p vendor/tecnickcom/tcpdf && \
    curl -L https://github.com/tecnickcom/TCPDF/archive/6.4.1.tar.gz | tar xz -C vendor/tecnickcom/tcpdf --strip-components=1

# Instala las dependencias de Composer
RUN composer clear-cache && \
    rm -rf vendor && \
    composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Instala y habilita el módulo MPM prefork y rewrite
RUN a2enmod mpm_prefork rewrite

# Crea el directorio de uploads y establece los permisos correctos
RUN mkdir -p /var/www/html/public/uploads && \
    chown -R www-data:www-data /var/www/html/public/uploads && \
    chmod -R 755 /var/www/html/public/uploads

RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads

RUN chown -R www-data:www-data /var/www/html

RUN echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini
# Expone el puerto en el que Apache escuchará.
EXPOSE 80

# Comando para iniciar Apache en primer plano.
CMD ["apache2-foreground"]