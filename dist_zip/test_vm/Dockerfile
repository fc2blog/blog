FROM ubuntu:focal

ENV DEBIAN_FRONTEND=noninteractive

RUN set -eux \
 && apt-get update -y \
 && apt-get upgrade -y \
 && apt-get install -y software-properties-common iproute2 vim git wget unzip locales ssl-cert \
 && sed -i -E 's/# (ja_JP.UTF-8)/\1/' /etc/locale.gen \
 && locale-gen \
 && rm -rf /tmp/*

RUN add-apt-repository -y ppa:ondrej/php \
 && add-apt-repository -y ppa:ondrej/apache2 \
 && apt-get update \
 && apt-get install -y apache2 mysql-server php8.0 libapache2-mod-php8.0 php8.0-intl php8.0-mbstring php8.0-gd php8.0-mysql php8.0-zip

ARG PUID=1000
ARG PGID=1000

RUN groupmod -o -g $PGID www-data && \
    usermod -o -u $PUID -g www-data www-data && \
    usermod --shell /bin/bash www-data

COPY 001-blog.conf /etc/apache2/sites-available/

RUN make-ssl-cert generate-default-snakeoil --force-overwrite \
 && a2enmod rewrite \
 && a2enmod ssl \
 && a2ensite 001-blog \
 && a2dissite 000-default.conf \
 && a2dissite default-ssl.conf

RUN mkdir /var/run/mysqld \
 && chmod 777 /var/run/mysqld

RUN sh -c "/usr/bin/mysqld_safe --user=mysql &" \
 && sleep 3 \
 && mysql_secure_installation -ppass -D \
 && echo "CREATE DATABASE fc2blog_db" | mysql \
 && echo "CREATE USER 'dbuser'@'127.0.0.1' IDENTIFIED BY 'd1B2p3a#s!s';" | mysql \
 && echo "GRANT ALL ON fc2blog_db.* TO 'dbuser'@'127.0.0.1';" | mysql

COPY fc2blog_dist.zip /var/www/html/
COPY fc2blog_installer.php /var/www/html/
RUN chown -R www-data:www-data /var/www/

COPY startup.sh /
