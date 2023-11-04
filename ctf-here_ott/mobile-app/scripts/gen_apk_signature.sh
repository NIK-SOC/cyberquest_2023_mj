#!/bin/sh

DIR=$(dirname $(readlink -f $0))

# password: Kv32mDbGSLTCCqLh0SyEaTE94RxSeieP7EHbGwif
keytool -genkey -v -keystore $DIR/../out/upload-keystore.jks -keyalg RSA \
        -keysize 2048 -validity 10000 -alias upload
