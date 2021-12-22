#!/usr/bin/env sh
# This script will download THINGs from URLs.
#
# The URL should be a URL that can be decompressed by bsdtar
# Example: .zip or .tar.gz

THING=$1
URL=$2
DIR=$THING

echo "Fetching $THING"
mkdir -p "$DIR"
# Files are always downloaded with a .compressed extension. bsdtar really doesn't care...
wget "$URL" -O "$THING.compressed"
bsdtar --strip-components=1 -xf "$THING".compressed -C "$DIR"
rm "$THING".compressed
