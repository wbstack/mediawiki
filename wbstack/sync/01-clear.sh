#!/usr/bin/env sh

echo "Clearing most of the git repo (except the files we want to keep)"

find ./ -mindepth 1 \
    -not -path "./composer.lock" \
    -not -path "./.dockerignore" \
    -not -path "./.hadolint.yml" \
    -not -path "./docker-compose.yml" \
    -not -path "./Dockerfile" \
    -not -path "./robots.txt" \
    -not -path "./health.php" \
    -not -path "./composer.*.json" \
    -not -path "./LocalSettings.php" \
    -not -path "./.git" -not -path "./.git/*" \
    -not -path "./.github" -not -path "./.github/*" \
    -not -path "./wbstack" -not -path "./wbstack/*" \
    -delete
