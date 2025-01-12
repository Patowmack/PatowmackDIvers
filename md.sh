cat head.html > $1.html
cat banner.html >> $1.html
cat navsite.html >> $1.html
pandoc $1.md -t html >> $1.html
cat footer.html >> $1.html


