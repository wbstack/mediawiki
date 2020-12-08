#!/usr/bin/env sh
# Simply runs the fetch.sh script in a docker container
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/tmp --entrypoint sh ghcr.io/wbstack/build-util:latest wbstack/fetch.sh