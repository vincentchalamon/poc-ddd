#!/bin/sh
set -e

# this script will, out of convenience, be ran from within the phpdoc/phpdoc container
# it generates RST files with UML diagrams from PHP source code
# to prevent extra noise, let's focus on Domain layers only

echo "Building UML templates..."
mkdir -p /data/.phpdoc/uml
echo ".. toctree::" > /data/.phpdoc/uml/index.rst
echo "   :hidden:" >> /data/.phpdoc/uml/index.rst
echo "   :titlesonly:" >> /data/.phpdoc/uml/index.rst
echo "" >> /data/.phpdoc/uml/index.rst

for file in $(find "/data/config/contexts/"  -type f ! -name "brew.php" -print | sort); do
    name=$(grep "\$services->load('App" $file | sed -E 's/.*(App\\[a-zA-Z\d\\]*)\\.*/\1/' | sed -E 's/(.*)\\$/\1/')
    title=$(echo $name | sed -E 's/\\/\\\\\\\\/g')
    ff=$(echo $name | sed 's/\\/-/g')
    echo "   $ff" >> /data/.phpdoc/uml/index.rst
    echo "$title" > /data/.phpdoc/uml/$ff.rst
    printf '%*s' "${#title}" '' | tr ' ' '=' >> /data/.phpdoc/uml/$ff.rst
    echo "" >> /data/.phpdoc/uml/$ff.rst
    echo "" >> /data/.phpdoc/uml/$ff.rst
    echo ".. phpdoc:class-diagram:: [?(@.namespace starts_with \""\\$name"\Domain\")]" >> /data/.phpdoc/uml/$ff.rst
done

echo "" >> /data/.phpdoc/uml/index.rst
echo "UML" >> /data/.phpdoc/uml/index.rst
echo "===" >> /data/.phpdoc/uml/index.rst
echo "" >> /data/.phpdoc/uml/index.rst

for file in $(find "/data/config/contexts/" -type f -print | sort); do
    name=$(grep "\$services->load('App" $file | sed -E 's/.*(App\\[a-zA-Z\d\\]*)\\.*/\1/' | sed -E 's/(.*)\\/\1/')
    title=$(echo $name | sed -E 's/\\?(App\\[a-zA-Z\d\\]*)\\/\1/')
    ff=$(echo $name | sed -E 's/\\\\/\\/g' | sed 's/\\/-/g')
    echo "-  \`$title <$ff.rst>\`__" >> /data/.phpdoc/uml/index.rst
done

exec /opt/phpdoc/bin/phpdoc "$@"
