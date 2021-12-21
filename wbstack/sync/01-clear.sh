#!/usr/bin/env sh
# Clears the git repo from fetched files and stuff..

echo "Clearing git repo (except the files that we maintain here)"

find ./ -mindepth 1 ! -regex '^./\(\.dockerignore\|\.git\|\.github\|\.hadolint\.yml\|wbstack\|docker\-compose\.yml\|Dockerfile\|robots\.txt\|health\.php\|composer\.\w+\.json\|LocalSettings\.php\)\(/.*\)?' -delete
