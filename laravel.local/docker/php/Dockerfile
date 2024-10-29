FROM php:8.2-fpm

USER root

# Установка обновлений и необходимых системных утилит
RUN apt-get update && \
    apt-get install -y \
        apt-utils \
        libpq-dev \
        bash \
        wget \
        curl \
        npm \
        tar

# Установка расширения pdo_pgsql для PHP
RUN docker-php-ext-install pdo pdo_pgsql

# Установка composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/

USER www-data:www-data
