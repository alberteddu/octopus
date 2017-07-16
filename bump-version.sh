#!/bin/bash

set -e

if [ $# -ne 1 ]; then
  echo "Usage: `basename $0` <tag>"
  exit 65
fi

TAG=$1

#
# Tag & build master branch
#
git checkout master
git tag ${TAG}
box build

#
# Copy executable file into GH pages
#
git checkout gh-pages

rm -f downloads/octopus-latest.phar
cp octopus.phar downloads/octopus-${TAG}.phar
cp octopus.phar downloads/octopus-latest.phar
git add downloads/octopus-${TAG}.phar
git add downloads/octopus-latest.phar

SHA1=$(openssl sha1 octopus.phar | sed 's/^.* //')

JSON='name:"octopus.phar"'
JSON="${JSON},sha1:\"${SHA1}\""
JSON="${JSON},url:\"http://alberteddu.github.io/octopus/downloads/octopus-${TAG}.phar\""
JSON="${JSON},version:\"${TAG}\""

#
# Update manifest
#
cat manifest.json | jsawk -a "this.push({${JSON}})" | python -mjson.tool > manifest.json.tmp
mv manifest.json.tmp manifest.json
git add manifest.json

git commit -m "Bump version ${TAG}"

#
# Go back to master
#
git checkout master

echo "New version created. Now you should run:"
echo "git push origin gh-pages"
echo "git push origin ${TAG}"
