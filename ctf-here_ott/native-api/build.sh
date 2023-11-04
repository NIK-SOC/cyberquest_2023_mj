#!/bin/sh

DIR=$(dirname $(readlink -f $0))

set -e

if [ "$1" = "build" ]; then
    podman build -t ctf-here_ott/native-api -f Dockerfile.build $DIR
fi

podman run --name ctf-here_ott_nativeapi ctf-here_ott/native-api
mkdir -p $DIR/out
podman cp ctf-here_ott_nativeapi:/build/pkg/ $DIR/out/
podman rm ctf-here_ott_nativeapi

echo "Done!"

