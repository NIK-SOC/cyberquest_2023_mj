FROM docker.io/golang:alpine AS builder

COPY . /build
WORKDIR /build

RUN go build -ldflags="-s -w" -o /tmp/hereott-redirect .

FROM scratch

COPY --from=builder /tmp/hereott-redirect /hereott-redirect
COPY assets/ /assets/

ENTRYPOINT ["/hereott-redirect"]
