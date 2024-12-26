FROM php:8.1-apache


RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl


RUN docker-php-ext-install pdo pdo_mysql zip


RUN a2enmod rewrite


RUN chown -R www-data:www-data /var/www/html


COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf


WORKDIR /var/www/html