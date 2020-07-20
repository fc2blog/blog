FROM php:7.4-apache

RUN set -eux \
 && apt-get update -y \
 && apt-get upgrade -y \
 && apt-get install -y git autoconf g++ libtool make libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-jpeg=/usr \
 && docker-php-ext-configure opcache --enable-opcache \
 && docker-php-ext-install opcache bcmath pdo_mysql gd exif zip gettext\
 && rm -rf /tmp/*

RUN a2enmod rewrite

