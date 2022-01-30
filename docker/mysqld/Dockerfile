FROM mariadb:10.5

ARG PUID=1000
ARG PGID=1000

RUN echo "-> $PUID"
RUN echo "-> $PGID"

RUN groupmod -o -g $PGID mysql && \
    usermod -o -u $PUID -g mysql mysql && \
    usermod --shell /bin/bash mysql

COPY mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf
