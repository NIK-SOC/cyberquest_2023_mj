FROM alpine:latest

RUN apk --no-cache add apache2 supervisor

COPY frontend/out/build/ /files/www-root/
COPY backend/bin/ointment /files/
COPY backend/usernames.txt /files/
COPY httpd.conf /etc/apache2/httpd.conf
COPY supervisord.conf /etc/supervisord.conf

EXPOSE 8080 1337

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
