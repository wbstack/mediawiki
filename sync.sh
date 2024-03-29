#!/usr/bin/env bash
# Syncs the whole git repo in the correct way
# This is probably the file that you want to run..

# exit when any command fails
set -e

BASEDIR=$(cd `dirname "$0"` && pwd)

cd $BASEDIR

Help()
{
   # Display Help
   echo "Syntax: sync.sh [-h|u]"
   echo "options:"
   echo "u     Composer update, as well as Composer install"
   echo "h     Print this Help."
   echo
   echo "Examples:"
   echo "      sync.sh"
   echo "      sync.sh -u"
}

# Set variable defaults
ALSO_COMPOSER_UPDATE=""

# Get the options
while getopts ":uh" option; do
   case $option in
      u)
         ALSO_COMPOSER_UPDATE="1";;
      h) # display Help
         Help
         exit;;
   esac
done

# Export vars for other scripts
export ALSO_COMPOSER_UPDATE=${ALSO_COMPOSER_UPDATE}

# includes filenames beginning with a '.' in the results of filename expansion (/*)
shopt -s dotglob

# Clears everything in the repo
echo "Delete the contents of the dist folder"
rm -rf ./dist/*

# Fetches things from the web
$BASEDIR/sync/pacman/pacman $BASEDIR

./sync_dist-persist.sh

# Does a composer install
$BASEDIR/sync/04-docker-composer.sh

# Adds shim to .php entrypoints
$BASEDIR/sync/05-docker-entrypoint-overrides.sh
