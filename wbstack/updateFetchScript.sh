#!/usr/bin/env bash

docker run --rm -it -v /$PWD/://tmp/src --entrypoint php php:7.4-cli //tmp/src/updateFetchScript.php
