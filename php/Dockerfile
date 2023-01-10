FROM php:8.1-apache

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

WORKDIR /var/www/html

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd xdebug intl pgsql pdo_pgsql mbstring ffi curl mcrypt memcache memcached opcache zip

RUN apt-get update && apt-get install -y software-properties-common npm nano
RUN npm install npm@latest -g && \
    npm install n -g && \
    n latest

RUN a2enmod ssl

RUN a2enmod rewrite
