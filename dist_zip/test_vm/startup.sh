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
echo "If you want exit. please exit or ctrl-D"

echo ==================================
echo "http://`ip -4 addr | grep -oP '(?<=inet\s)\d+(\.\d+){3}' |grep -v 127.0.0.1`/admin/common/install"
echo ==================================

