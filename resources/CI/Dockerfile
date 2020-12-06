# RUNTIME
FROM php:8.0

RUN apt-get update -y \
&& apt-get install -y git zip unzip \
&& pecl install xdebug \
&& docker-php-ext-enable xdebug \
&& docker-php-ext-install pdo

WORKDIR app

COPY ./php.ini /usr/local/etc/php/

# COMPOSER
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
