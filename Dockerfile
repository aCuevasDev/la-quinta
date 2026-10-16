FROM php:8.3-apache

# Extensión de MySQL para PDO (acceso a datos con consultas preparadas)
RUN docker-php-ext-install pdo_mysql

# El código vive en src/, que es la raíz web (RNF-08)
COPY src/ /var/www/html/

# Nunca mostrar errores técnicos de PHP al usuario (RNF-05); sí registrarlos
RUN printf 'display_errors=Off\nlog_errors=On\nerror_reporting=E_ALL\n' > /usr/local/etc/php/conf.d/zz-la-quinta.ini

EXPOSE 80
