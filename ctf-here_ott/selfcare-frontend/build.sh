#!/bin/sh

DIR=$(dirname $(readlink -f $0))

set -e

if [ "$1" = "build" ]; then
    podman build -t ctf-here_ott/selfcare_frontend -f Dockerfile.build $DIR
fi

podman run --name ctf-here_ott_selfcarefrontend ctf-here_ott/selfcare_frontend
mkdir -p $DIR/out
podman cp ctf-here_ott_selfcarefrontend:/app/dist/ $DIR/out/
podman rm ctf-here_ott_selfcarefrontend

echo "Done!"

