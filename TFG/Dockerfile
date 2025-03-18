#Usa la imagen oficial de PHP con Apache
FROM php:8.1-apache

#Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

#Habilitar mod_rewrite para que .htaccess funcione
RUN a2enmod rewrite

#Copiar todo el contenido de la carpeta TFG al servidor web
COPY . /var/www/html/

#Establecer permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

#Exponer el puerto 80
EXPOSE 80

#Iniciar Apache
CMD ["apache2-foreground"]
