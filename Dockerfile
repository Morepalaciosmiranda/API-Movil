# Usa una imagen base oficial de PHP con Apache.
FROM php:8.1-apache

# Establece el directorio de trabajo en el contenedor.
WORKDIR /var/www/html

# Copia el código fuente de tu aplicación al contenedor.
COPY . .

# Copia el archivo de configuración personalizado para Apache
COPY ./config/apache2.conf /etc/apache2/apache2.conf

# Instala las extensiones necesarias de PHP. (añade más si tu proyecto las requiere)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Expone el puerto en el que Apache escuchará.
EXPOSE 80

# Comando para iniciar Apache en primer plano.
CMD ["apache2-foreground"]
