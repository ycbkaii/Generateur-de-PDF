#!/bin/bash
cd img
imglist=$(ls *.svg | cut -d "." -f 1)
for img in $imglist
do
magick $img.svg $img.png
magick -colorspace gray $img.png $img.png
magick $img.png -shave 45x45 $img.png
magick $img.png -resize 200x200  $img.png
done