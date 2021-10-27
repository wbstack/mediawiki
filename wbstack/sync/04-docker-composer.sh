#!/usr/bin/env sh

echo "Performing needed composer installations"

SCRIPT_COMPOSER_CACHE=${COMPOSER_CACHE_DIR:-$HOME/.cache/composer}

mkdir -p ${COMPOSER_CACHE_DIR:-$HOME/.cache/composer}

# composer install
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/app \
  --volume $SCRIPT_COMPOSER_CACHE:/tmp/cache \
  --entrypoint composer -w /app \
  docker-registry.wikimedia.org/releng/composer-package-php74:0.3.0-s7 install --no-dev --no-progress --optimize-autoloader --ignore-platform-reqs

# composer update
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/app \
  --volume $SCRIPT_COMPOSER_CACHE:/tmp/cache \
  --entrypoint composer -w /app \
  docker-registry.wikimedia.org/releng/composer-package-php74:0.3.0-s7 update --no-dev --no-progress --optimize-autoloader --ignore-platform-reqs


# Sometimes composer git clones things rather than using zips.
# so make sure to remove the .git directories so that things get committed correctly.
# https://github.com/wbstack/mediawiki/issues/5
echo "Cleaning up any .git directories in composer packages..."
find ./ -mindepth 1 -regex '^./vendor/.*/.*/\.git\(/.*\)?' -delete

