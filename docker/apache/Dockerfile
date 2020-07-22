FROM php:7.4-apache

RUN set -eux \
 && apt-get update -y \
 && apt-get upgrade -y \
 && apt-get install -y git autoconf g++ libtool make libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libicu-dev locales ssl-cert \
 && sed -i -E 's/# (ja_JP.UTF-8)/\1/' /etc/locale.gen \
 && locale-gen \
 && docker-php-ext-configure gd --with-jpeg=/usr \
 && docker-php-ext-configure opcache --enable-opcache \
 && docker-php-ext-install opcache bcmath pdo_mysql gd exif zip gettext intl \
 && rm -rf /tmp/*

RUN a2enmod rewrite \
 && a2enmod ssl \
 && a2ensite default-ssl


