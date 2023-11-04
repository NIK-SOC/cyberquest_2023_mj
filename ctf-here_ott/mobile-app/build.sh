#!/bin/sh

DIR=$(dirname $(readlink -f $0))

set -e

if [ "$1" = "build" ]; then
    podman build -t ctf-here_ott/mobileapp -f Dockerfile.build $DIR
fi

echo "Building the APK..."

podman run --name ctf-here_ott_mobileapp ctf-here_ott/mobileapp
mkdir -p $DIR/out
podman cp ctf-here_ott_mobileapp:/build/hereott/build/app/outputs/flutter-apk/app-release.apk $DIR/out
podman cp ctf-here_ott_mobileapp:/tmp/hereott-symbols $DIR/out
podman rm ctf-here_ott_mobileapp

echo "Done!"

