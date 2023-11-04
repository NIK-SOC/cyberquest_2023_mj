#!/bin/sh

DIR=$(dirname $(readlink -f $0))

set -e

if [ "$1" = "build" ]; then
    podman build -t ctf-here_ott/backend -f Dockerfile.build $DIR
fi

podman run --name ctf-here_ott_backend ctf-here_ott/backend
mkdir -p $DIR/bin
podman cp ctf-here_ott_backend:/tmp/hereott $DIR/bin/hereott
podman rm ctf-here_ott_backend
strip $DIR/bin/hereott

echo "Done!"

