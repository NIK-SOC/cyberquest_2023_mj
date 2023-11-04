FROM alpine:latest

RUN apk add --no-cache php82 php82-fpm php82-mysqli nginx supervisor mariadb mariadb-client

COPY backup.sql /tmp/backup.sql
COPY ./init-mysql.sh /tmp/init-mysql.sh

RUN adduser -D -g 'www' www && \
    mkdir -p /run/mysqld && \
    chown -R mysql:mysql /run/mysqld && \
    chown -R www:www /var/log/php82 && \
    chmod +x /tmp/init-mysql.sh && \
    /tmp/init-mysql.sh && \
    rm /tmp/init-mysql.sh && \
    rm /tmp/backup.sql && \
    echo "* * * * * wget -q -O - http://127.0.0.1:8080/wp-cron.php > /dev/null 2>&1" >> crontab.tmp && \
    crontab crontab.tmp && \
    rm crontab.tmp

COPY --chown=www:www www-root /www
COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisord.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]