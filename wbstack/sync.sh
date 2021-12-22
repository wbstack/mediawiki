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
