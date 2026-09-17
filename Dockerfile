FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod headers \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf

COPY index.php index.html /var/www/html/
COPY api/ /var/www/html/api/
COPY assets/ /var/www/html/assets/
COPY admin/ /var/www/html/admin/
COPY morador/ /var/www/html/morador/
COPY prestador/ /var/www/html/prestador/
RUN chown -R www-data:www-data /var/www/html
