FROM php:7.1

WORKDIR app

RUN apt-get update -yqq

RUN apt-get install -yqq git zip unzip gnupg graphviz libicu-dev libicu57 zlib1g-dev libxslt1-dev

RUN pecl install apcu xdebug \
&& docker-php-ext-enable apcu xdebug \
&& docker-php-ext-install zip intl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

