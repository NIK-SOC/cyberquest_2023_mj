FROM alpine:latest
# we need Alpine because of SSL, scratch won't do

RUN adduser -D -u 1000 ctf

COPY --chown=ctf:ctf backend/bin/hereott /files/
COPY --chown=ctf:ctf backend/assets /files/assets

WORKDIR /files

CMD ["/files/hereott"]
