#!/usr/bin/env bash
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/app --entrypoint php php:7.4-cli /app/wbstack/entrypoint-overrides.php
