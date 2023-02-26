#!/usr/bin/bash

SRCDAT=$1

src=$(ls $1/*.txt)

mkdir pdf
# Convertion des images
docker container run --rm -v "$PWD":/work sae103-imagick "./convertimage.sh"
for fic in $src
do
# Execution du script pour extraire les données
docker container run --rm -v "$PWD":/work sae103-php php -f script.php "$fic"

nom=$(cat iso.dat | tr -d "\ ")
#les qrcode 
./qrcode.sh $nom

# la fusion
docker container run --rm -v "$PWD":/work sae103-php:latest php -f Page.php > "$nom.html"

done
# Compression
./html2pdf.sh
rm *.html
rm -f *.dat
tar czvf resultat.tar.gz pdf