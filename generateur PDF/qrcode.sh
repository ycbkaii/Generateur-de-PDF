#!/usr/bin/bash
code=$1

#les qrcode 
mkdir img
docker container run --rm -v "$PWD":/work sae103-qrcode qrcode "https//bigbrain.biz/$1" -o "img/$1.png"