#!/usr/bin/env sh
docker build --tag wbstack/mediawiki/nufetch:7.4-cli --file ./wbstack/sync/nufetch.Dockerfile ./wbstack/sync/
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/app --entrypoint php wbstack/mediawiki/nufetch:7.4-cli /app/wbstack/sync/nufetch.php
