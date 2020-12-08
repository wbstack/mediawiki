#!/usr/bin/env bash
# This must be run in the sync directory!
docker run --rm -it -v /$PWD/://tmp/src --entrypoint php php:7.4-cli //tmp/src/updateFetchScript.php
