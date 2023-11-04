FROM rockylinux:9.2 AS builder

RUN dnf install -y gcc make git \
    && git clone --depth 1 --single-branch --branch master https://github.com/johnsonjh/ynetd.git /ynetd \
    && cd /ynetd \
    && make

FROM rockylinux:9.2

RUN adduser -m -s /bin/false -r challenger && \
    chown -R root:root /home/challenger && \
    chmod -R 555 /home/challenger

COPY --from=builder /ynetd/ynetd /home/challenger/ynetd
COPY out/oracle /home/challenger/oracle
COPY flag.txt /home/challenger/flag.txt

RUN chmod 555 /tmp && \
    chmod 555 /var/tmp && \
    chmod 555 /dev && \
    chmod 555 /run

USER challenger
WORKDIR /home/challenger

CMD ["/bin/sh", "-c", "/home/challenger/ynetd -p ${CHALLENGE_PORT} /home/challenger/oracle -se y"]