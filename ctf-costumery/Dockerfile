FROM alpine:latest AS builder

RUN apk --no-cache add gcc make libc-dev git \
    && git clone --depth 1 --single-branch --branch master https://github.com/johnsonjh/ynetd.git /ynetd \
    && cd /ynetd \
    && make

FROM alpine:latest

RUN adduser -D -h /home/challenger -s /bin/false challenger && \
    chown -R root:root /home/challenger && \
    chmod -R 555 /home/challenger

COPY --from=builder /ynetd/ynetd /home/challenger/ynetd
COPY out/costumery /home/challenger/costumery
COPY flag.txt /home/challenger/flag.txt

RUN chmod 555 /tmp && \
    chmod 555 /var/tmp && \
    chmod 555 /dev && \
    chmod 555 /run && \
    rm /bin/grep && \
    rm /sbin/ip && \
    rm /usr/bin/lsof && \
    rm /bin/netstat && \
    rm /bin/ps
    # to make things harder

USER challenger
CMD ["/bin/sh", "-c", "[ -n \"$FLAG\" ] && /home/challenger/ynetd -p ${CHALLENGE_PORT} /home/challenger/costumery -se y || echo 'FLAG env var is not set'"]
