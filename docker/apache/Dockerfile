FROM php:8.1-apache

RUN set -eux \
 && apt-get update -y \
 && apt-get upgrade -y \
 && curl -fsSL https://deb.nodesource.com/setup_16.x | bash - \
 && apt-get install -y git autoconf g++ libtool make mariadb-client wget vim inetutils-ping \
 && apt-get install -y libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libicu-dev locales ssl-cert libfreetype6-dev \
 && apt-get install -y nodejs \
 && apt-get install -y libgbm-dev gconf-service libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxss1 libxtst6 libnss3 libasound2 libatk1.0-0 libc6 ca-certificates fonts-liberation lsb-release xdg-utils \
 # bellow 2 lines are tweak for Arm64 support. Wanna be remove in future.
 && apt-get install -y chromium \
 && ln -s /usr/bin/chromium /usr/bin/chromium-browser \
 && sed -i -E 's/# (ja_JP.UTF-8)/\1/' /etc/locale.gen \
 && locale-gen \
 && docker-php-ext-configure gd --with-jpeg=/usr --with-freetype=/usr \
 && docker-php-ext-configure opcache --enable-opcache \
 && docker-php-ext-install opcache bcmath pdo_mysql gd exif zip gettext intl \
 && pecl install xdebug \
 && docker-php-ext-enable xdebug \
 && rm -rf /tmp/*

ARG PUID=1000
ARG PGID=1000

RUN echo "-> $PUID"
RUN echo "-> $PGID"

RUN groupmod -o -g $PGID www-data && \
    usermod -o -u $PUID -g www-data www-data && \
    usermod --shell /bin/bash www-data

COPY ./etc/apache2/sites-available/001-blog.conf /etc/apache2/sites-available/
COPY ./usr/local/etc/php/conf.d/docker-php-ext-xdebug-debug-enable.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug-debug-enable.ini
COPY ./usr/local/etc/php/conf.d/php-error-settings.ini /usr/local/etc/php/conf.d/php-error-settings.ini
COPY ./etc/apache2/apache2.conf /etc/apache2

RUN make-ssl-cert generate-default-snakeoil --force-overwrite

RUN a2enmod rewrite \
 && a2enmod ssl \
 && a2dissite default-ssl \
 && a2dissite 000-default \
 && a2ensite 001-blog.conf

