#!/usr/bin/bash
liste_html=$(ls *.html | cut -d "." -f 1)
for nom in $liste_html
do
	docker container run --rm -v "$PWD":/work sae103-html2pdf "html2pdf $nom.html pdf/$nom.pdf"
done