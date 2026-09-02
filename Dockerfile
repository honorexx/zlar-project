FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod headers \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
