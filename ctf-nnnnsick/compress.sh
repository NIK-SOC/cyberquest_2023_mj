#!/bin/sh

DIR=$(dirname $(readlink -f $0))

xz -z -9 -T 0 -k $DIR/out/challenge.txt --stdout > $DIR/out/challenge.xz