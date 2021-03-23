#!/usr/bin/env sh
# Syncs the whole git repo in the correct way
# This is probably the file that you want to run..

# This script should only be run from the mediawiki directory
# as I didn't make the paths nice in the shell scripts...
BASEDIR=$(cd `dirname "$0"` && pwd)
if [ "${BASEDIR#$PWD}" = "/wbstack" ]; then
    echo "Running from the mediawiki directory, can continue :)"
else
    echo "ERROR: this script must be run from the mediawiki directory :("
    exit
fi;

# Clears everything in the repo
$BASEDIR/sync/clear.sh

# Fetches things from the web
$BASEDIR/sync/docker-fetch.sh

# Removes some not needed things from the things fetched
$BASEDIR/sync/less-files.sh

# Does a composer install
$BASEDIR/sync/docker-composer.sh

# Adds shim to .php entrypoints
$BASEDIR/sync/docker-entrypoint-overrides.sh
