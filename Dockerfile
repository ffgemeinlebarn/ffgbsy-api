FROM php:8.3-fpm-alpine

RUN apk update
RUN apk add git
RUN apk add icu-dev
RUN apk add icu-data-full
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-enable mysqli
RUN docker-php-ext-configure intl && docker-php-ext-install intl

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

COPY . /var/www/html
RUN composer install --prefer-dist --no-progress --no-interaction
RUN composer dump-autoload

RUN chown -R www-data:www-data /var/www
