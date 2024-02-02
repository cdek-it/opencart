FROM php:7.4-apache

RUN apt-get update && apt-get install -y \
      libzip-dev libpng-dev libjpeg-dev && \
    docker-php-ext-configure gd --with-jpeg &&\
    docker-php-ext-install -j$(nproc) zip gd mysqli pdo pdo_mysql

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
