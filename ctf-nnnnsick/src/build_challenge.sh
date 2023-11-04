#!/bin/sh

# get script's directory
DIR=$(dirname $(readlink -f $0))

echo "Building test..."
make
cc -O2 -pipe -Wno-attributes -o /tmp/test $DIR/test.c -lm -L. -lg722
mkdir -p $DIR/../out
LD_LIBRARY_PATH=$DIR /tmp/test $DIR/../assets/flag_converted.g722 /tmp/test.raw > $DIR/../out/challenge.txt
make clean
rm /tmp/test
echo "Done, find the challenge in $DIR/../out/challenge.txt"