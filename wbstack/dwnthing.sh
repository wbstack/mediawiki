#!/usr/bin/env sh
THING=$1
URL=$2

DIR=$THING
if [ "$THING" = "core" ]; then
    DIR=.
fi

echo "Fetching $THING"
mkdir -p "$DIR"
curl -s "$URL" -o "$THING.compressed"
bsdtar --strip-components=1 -xf "$THING".compressed -C "$DIR"
rm "$THING".compressed

if [ "$THING" = "core" ]; then
    rm -rf ./extensions/*
    rm -rf ./skins*/
fi