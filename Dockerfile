FROM php:8.2-apache
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html/uploads
