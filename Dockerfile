FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip libonig-dev libzip-dev zip curl \
    && docker-php-ext-install pdo_mysql mbstring zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
