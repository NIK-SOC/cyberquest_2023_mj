#!/bin/sh

DIR=$(dirname $(readlink -f $0))

set -e

if [ "$1" = "build" ]; then
    podman build -t ctf-application_ointment/frontend -f Dockerfile.build $DIR
fi

rm -rf $DIR/out
mkdir -p $DIR/out

podman run --name ctf-application_ointment_frontend ctf-application_ointment/frontend
podman cp ctf-application_ointment_frontend:/build/build $DIR/out
podman rm ctf-application_ointment_frontend

echo "Done!"
