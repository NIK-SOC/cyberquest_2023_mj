#!/bin/sh

DIR=$(dirname $(readlink -f $0))

set -e

if [ "$1" = "build" ]; then
    podman build -t ctf-application_ointment/backend -f Dockerfile.build $DIR
fi

podman run --name ctf-application_ointment_backend ctf-application_ointment/backend
mkdir -p $DIR/bin
podman cp ctf-application_ointment_backend:/tmp/ointment $DIR/bin/ointment
podman rm ctf-application_ointment_backend
strip $DIR/bin/ointment

echo "Done!"

