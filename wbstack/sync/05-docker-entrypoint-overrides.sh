#!/usr/bin/env sh
# Simply runs the entrypoint-overrides.php script in a docker container
docker run --rm -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/app --entrypoint php php:7.4-cli /app/wbstack/sync/05-entrypoint-overrides.php
