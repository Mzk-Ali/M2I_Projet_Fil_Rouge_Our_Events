FROM php:8.4-fpm

RUN apt-get update && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libpng-dev

RUN docker-php-ext-install opcache

RUN pecl install apcu

RUN docker-php-ext-enable apcu

RUN pecl install xdebug

RUN docker-php-ext-enable xdebug

COPY php.ini /usr/local/etc/php/php.ini