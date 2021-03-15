#!/usr/bin/env sh

/usr/bin/mysqld_safe --user=mysql &

APACHE_RUN_DIR=/var/run/apache2 \
APACHE_PID_FILE=/var/run/apache2/pid \
APACHE_RUN_USER=www-data \
APACHE_RUN_GROUP=www-data \
APACHE_LOG_DIR=/var/log/apache2/ \
APACHE_CONFDIR=/etc/apache2 \
/usr/sbin/apache2  -DFOREGROUND &

sleep 3

echo ==================================
echo "Please use this settings in installer"
echo "DB_HOST          : 127.0.0.1"
echo "DB_PORT          : 3306"
echo "DB_DATABASE NAME : fc2blog_db"
echo "DB_USER          : dbuser"
echo "DB_PASSWORD      : d1B2p3a#s!s"
echo "DB_CHARSET       : UTF8MB4"
echo "HTTP_PORT        : 80"
echo "HTTPS_PORT       : 443"
echo ""
echo "http://`ip -4 addr | grep -oP '(?<=inet\s)\d+(\.\d+){3}' |grep -v 127.0.0.1`/fc2blog_installer.php"
echo ==================================

