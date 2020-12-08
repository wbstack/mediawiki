#!/usr/bin/env sh
# This script will download THINGs from URLs.
#
# The THING should be "core" or a path within core for a component
# Example: "core" or "extensions/Wikibase" or "skins/Vector"
# "core" should always be the first thing fetched, as it causes the extensions and skins dir to be emptied
#
# The URL should be a URL that can be decompressed by bsdtar
# Example: .zip or .tar.gz

THING=$1
URL=$2

DIR=$THING
if [ "$THING" = "core" ]; then
    DIR=.
fi

echo "Fetching $THING"
mkdir -p "$DIR"
# Files are always downloaded with a .compressed extension. bsdtar really doesn't care...
curl -s "$URL" -o "$THING.compressed"
bsdtar --strip-components=1 -xf "$THING".compressed -C "$DIR"
rm "$THING".compressed

# core by default has some things in it that we don't want, so delete them
if [ "$THING" = "core" ]; then
    rm -rf ./extensions/*
    rm -rf ./skins*/
fi