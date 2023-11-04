#!/bin/sh

echo "Starting MySQL..."

mysql_install_db --datadir=/var/lib/mysql --user=mysql

/usr/bin/mysqld_safe --datadir=/var/lib/mysql --user=mysql &
MYSQL_PID=$!

echo "Waiting for MySQL to start..."
while ! mysqladmin ping --silent; do
    echo "Waiting for MySQL to start..."
    sleep 1
done

echo "MySQL started."
mysql < /tmp/backup.sql

kill $MYSQL_PID
