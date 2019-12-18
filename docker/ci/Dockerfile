FROM php:7.4

WORKDIR app

RUN apt-get update -yqq

RUN apt-get install -yqq git zip unzip libzip-dev zlib1g-dev

RUN pecl install apcu xdebug
RUN docker-php-ext-enable apcu xdebug
RUN docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

