FROM php:8.4-fpm

RUN apt-get update && \
    apt-get install -y \
        curl \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        unzip \
        zip \
    && docker-php-ext-install \
        gd \
        mbstring \
        opcache \
        pdo \
        pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && rm -rf /var/lib/apt/lists/*

COPY php.ini /usr/local/etc/php/php.ini