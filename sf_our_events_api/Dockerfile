FROM php:8.4-fpm

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

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
    && if php -m | grep -qi xdebug; then docker-php-ext-enable xdebug; fi \
    && rm -rf /var/lib/apt/lists/*

COPY php.ini /usr/local/etc/php/php.ini

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
