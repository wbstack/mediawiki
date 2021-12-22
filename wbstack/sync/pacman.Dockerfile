FROM alpine:3.15

RUN apk add \
  bash \
  jq \
  libarchive-tools \
  yq

ENTRYPOINT bash
