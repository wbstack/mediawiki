#!/usr/bin/env bash

set -euo pipefail

REPOSITORY_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)

extract_wikiman_patch_urls() {
    yq -r '
      (
        .mediawikiPatches[]?.url,
        .extensions[].patches[]?.url,
        .skins[].patches[]?.url
      )
      | select(. != null and . != "")
    ' "$1"
}

extract_pacman_patch_urls() {
    yq -r '
      .[].patchUrls[]?
      | select(. != null and . != "")
    ' "$1"
}

diff \
    <(extract_wikiman_patch_urls "${REPOSITORY_DIR}/wikiman.yaml" | sort) \
    <(extract_pacman_patch_urls "${REPOSITORY_DIR}/pacman.yaml" | sort)
