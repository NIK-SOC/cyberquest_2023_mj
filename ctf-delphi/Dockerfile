FROM ghcr.io/void-linux/void-glibc:20231003R1 AS builder

RUN xbps-install -Syu -y \
    gcc \
    make \
    git \
    && rm -rf /var/cache/xbps \
    && git clone --depth 1 --single-branch --branch master https://github.com/johnsonjh/ynetd.git /ynetd \
    && cd /ynetd \
    && make

FROM ghcr.io/void-linux/void-glibc:20231003R1

RUN xbps-install -Syu -y shadow && \
    rm -rf /var/cache/xbps \
    && useradd -m -d /home/challenger -s /bin/false challenger && \
    chown -R root:root /home/challenger && \
    chmod -R 555 /home/challenger

COPY --from=builder /ynetd/ynetd /home/challenger/ynetd
COPY out/delphi /home/challenger/delphi
COPY flag.txt /home/challenger/flag.txt

RUN chmod 555 /tmp && \
    chmod 555 /var/tmp && \
    chmod 555 /dev && \
    chmod 555 /run

USER challenger
WORKDIR /home/challenger

CMD ["/bin/sh", "-c", "/home/challenger/ynetd -p ${CHALLENGE_PORT} /home/challenger/delphi -se y"]