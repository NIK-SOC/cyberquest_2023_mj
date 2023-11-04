#!/bin/sh

if ! command -v openssl &> /dev/null
then
    echo "openssl could not be found"
    exit 1
fi

DIR=$(dirname "$(readlink -f "$0")")

cat > "$DIR/../out/openssl.cnf" <<EOL
[req]
default_bits = 2048
default_md = sha256
prompt = no
encrypt_key = no
distinguished_name = dn
x509_extensions = v3_req

[dn]
C = MO
O = cyberquest.honeylab.hu
CN = hereott.honeylab.hu
ST = Meseorszag
OU = HereOtt

[v3_req]
basicConstraints = CA:false
subjectKeyIdentifier = hash
authorityKeyIdentifier = keyid,issuer
subjectAltName = @alt_names

[alt_names]
DNS.1 = 10.10.1.11
DNS.2 = 10.10.2.11
DNS.3 = 10.10.3.11
DNS.4 = 10.10.4.11
DNS.5 = 10.10.5.11
DNS.6 = 10.10.6.11
DNS.7 = 10.10.7.11
DNS.8 = 10.10.8.11
DNS.9 = 10.10.9.11
DNS.10 = hereott.honeylab.hu
EOL

openssl ecparam -genkey -name prime256v1 -out "$DIR/../out/key.pem"

openssl req -new -sha256 -key "$DIR/../out/key.pem" -out "$DIR/../out/csr.csr" -config "$DIR/../out/openssl.cnf"

openssl x509 -req -sha256 -days 365 -in "$DIR/../out/csr.csr" -signkey "$DIR/../out/key.pem" -out "$DIR/../out/cert.pem" -extensions 'v3_req' -extfile "$DIR/../out/openssl.cnf"

openssl req -in "$DIR/../out/csr.csr" -text -noout | grep -i "Signature.*SHA256" && echo "All is well" || echo "This certificate will stop working in 2017! You must update OpenSSL to generate a widely-compatible certificate"
