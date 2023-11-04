#!/bin/sh

DIR=$(dirname $(readlink -f $0))

zip -j -r $DIR/../out/challenge.zip $DIR/../out/oracle $DIR/../Dockerfile $DIR/../out/flag.txt