FROM alpine:3.15

RUN apk add \
  bash \
  # coreutils for a version of realpath with a -m option
  coreutils \
  jq \
  libarchive-tools \
  yq

ENTRYPOINT bash
