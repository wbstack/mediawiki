#!/usr/bin/env sh
# Syncs the whole git repo in the correct way
# This is probably the file that you want to run..

# Clears everything in the repo
./wbstack/clear.sh

# Fetches things from the web
./wbstack/docker-fetch.sh

# Removes some not needed things from the things fetched
./wbstack/less-files.sh

# Does a composer install
./wbstack/docker-composer.sh

# Removes some not needed things from the things fetched
./wbstack/docker-entrypoint-overrides.sh