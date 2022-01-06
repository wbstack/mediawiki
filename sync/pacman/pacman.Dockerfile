FROM alpine:3.15

RUN apk add \
  bash \
  # coreutils for a version of realpath with a -m option
  coreutils \
  jq \
  libarchive-tools \
  rsync \
  yq

COPY pacman-script /usr/bin/pacman-script

ENTRYPOINT ["pacman-script"]
