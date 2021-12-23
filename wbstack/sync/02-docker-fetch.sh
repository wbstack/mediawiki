#!/usr/bin/env sh

# Simply runs the fetch.sh script in a docker container
docker run --rm -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/tmp --entrypoint sh ghcr.io/wbstack/docker-build-util:latest wbstack/sync/02-fetch.sh