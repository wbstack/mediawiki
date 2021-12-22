#!/usr/bin/env sh
# Syncs the whole git repo in the correct way
# This is probably the file that you want to run..

BASEDIR=$(cd `dirname "$0"` && pwd)

cd $BASEDIR/..

# Clears everything in the repo
$BASEDIR/sync/01-clear.sh

# Fetches things from the web
$BASEDIR/sync/02-docker-fetch.sh

# Removes some not needed things from the things fetched
$BASEDIR/sync/03-less-files.sh

# Does a composer install
$BASEDIR/sync/04-docker-composer.sh

# Adds shim to .php entrypoints
$BASEDIR/sync/05-docker-entrypoint-overrides.sh
