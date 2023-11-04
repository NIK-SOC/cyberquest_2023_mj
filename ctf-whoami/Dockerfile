FROM ghcr.io/void-linux/void-glibc:latest as builder

RUN xbps-install -Syu -y && \
    xbps-install -Syu -y go gcc pcre-devel pkg-config && \
    rm -rf /var/cache/xbps/*

COPY backend/src/ /src/
COPY frontend/ /src/frontend/

RUN cd /src && go build -ldflags "-s -w -extldflags=-static" -o /tmp/whoami

FROM scratch

COPY --from=builder /tmp/whoami /whoami
COPY frontend/out/dist /frontend/
COPY backend/assets/ /assets/

CMD ["/whoami"]