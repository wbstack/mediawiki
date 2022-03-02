#!/usr/bin/env bash
# Only copies dist-persist folder to sync
# Useful if you only require to sync internal api changes
set -e

echo "Copy required files into the 'dist' dir"
cp -rv ./dist-persist/* ./dist/