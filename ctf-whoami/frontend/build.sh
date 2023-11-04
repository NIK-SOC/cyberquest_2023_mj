#!/bin/sh

DIR=$(dirname $(readlink -f $0))

set -e

if [ "$1" = "build" ]; then
    podman build -t ctf-whoami/frontend -f Dockerfile.build $DIR
fi

podman run --name ctf-whoami_frontend ctf-whoami/frontend
mkdir -p $DIR/out
podman cp ctf-whoami_frontend:/app/dist/ $DIR/out/
podman rm ctf-whoami_frontend

echo "Done!"

