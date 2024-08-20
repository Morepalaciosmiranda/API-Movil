# Usa una imagen base oficial de PHP con Apache.
FROM php:8.1-apache

# Establece el directorio de trabajo en el contenedor.
WORKDIR /var/www/html

# Copia el código fuente de tu aplicación al contenedor.
COPY . .

# Instala las extensiones necesarias de PHP. (añade más si tu proyecto las requiere)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Instala dependencias necesarias para las extensiones de SQL Server
RUN apt-get update && apt-get install -y \
    unixodbc-dev \
    libgssapi-krb5-2 \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

# Instala y habilita el módulo MPM prefork y otras configuraciones de Apache
RUN a2enmod mpm_prefork
RUN a2enmod rewrite

# Instala Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Expone el puerto en el que Apache escuchará.
EXPOSE 80

# Comando para iniciar Apache en primer plano.
CMD ["apache2-foreground"]
