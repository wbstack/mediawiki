#!/usr/bin/env sh
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/app --entrypoint php php:7.4-cli /app/wbstack/sync/nuget.php
