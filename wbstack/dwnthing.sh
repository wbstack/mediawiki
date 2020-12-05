#!/usr/bin/env sh
THING=$1
URL=$2

DIR=$THING
if [ "$THING" = "core" ]; then
    DIR=.
fi

echo "Fetching $THING"
mkdir -p "$DIR"
curl -s "$URL" -o "$THING.zip"
bsdtar --strip-components=1 -xf "$THING".zip -C "$DIR"
rm "$THING".zip

if [ "$THING" = "core" ]; then
    rm -rf ./extensions/*
fi