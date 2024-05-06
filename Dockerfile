FROM php:8.3-fpm-alpine

RUN apk add icu-dev 
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-enable mysqli
RUN docker-php-ext-configure intl && docker-php-ext-install intl

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer/composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html
RUN composer install --prefer-dist --no-progress --no-interaction

RUN chown -R www-data:www-data /var/www