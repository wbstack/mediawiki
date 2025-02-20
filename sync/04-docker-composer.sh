#!/usr/bin/env sh

SCRIPT_COMPOSER_CACHE=${COMPOSER_CACHE_DIR:-$HOME/.cache/composer}

mkdir -p ${COMPOSER_CACHE_DIR:-$HOME/.cache/composer}


## T302558 Pre-installing composer-merge-plugin
#
# Because of https://github.com/wikimedia/composer-merge-plugin/issues/202
# We are required to install the composer-merge plugin separately
# This avoids the unwanted update that happens on a fresh install
#
# This means for now, the wikimedia/composer-merge-plugin version needs to be defined here
COMPOSER_MERGE_PLUGIN_VERSION=v2.0.1

composer_in_docker () {
  docker run --rm -u $(id -u ${USER}):$(id -g ${USER}) -v "$COMPOSER_WORK_DIR":/app \
  --volume "$SCRIPT_COMPOSER_CACHE":/tmp/cache \
  --entrypoint composer -w /app \
  docker-registry.wikimedia.org/releng/composer-package-php81:8.1.30-s1 "$@"
}

COMPOSER_WORK_DIR=$(mktemp -d -p "$DIR")
composer_in_docker require wikimedia/composer-merge-plugin:$COMPOSER_MERGE_PLUGIN_VERSION

# Copy the temporary vendor folder to clean dist/
cp -r "$COMPOSER_WORK_DIR"/vendor "$PWD"/dist

# composer install
COMPOSER_WORK_DIR="$PWD"/dist
echo "Performing needed composer installations"
composer_in_docker install --no-dev --no-progress --optimize-autoloader

# composer update (When ALSO_COMPOSER_UPDATE = 1)
if [ "${ALSO_COMPOSER_UPDATE}" = "1" ]; then
    echo "Performing composer update"
    composer_in_docker update --no-dev --no-progress --optimize-autoloader
    cp dist/composer.lock dist-persist/
else
    echo "SKIPPING: composer update (As you didn't request it)"
fi;

# Sometimes composer git clones things rather than using zips.
# so make sure to remove the .git directories so that things get committed correctly.
# https://github.com/wbstack/mediawiki/issues/5
echo "Cleaning up any .git directories in composer packages..."
find ./dist -mindepth 1 -regex '^./dist/vendor/.*/.*/\.git\(/.*\)?' -delete
