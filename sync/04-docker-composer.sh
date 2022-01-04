#!/usr/bin/env sh

SCRIPT_COMPOSER_CACHE=${COMPOSER_CACHE_DIR:-$HOME/.cache/composer}

mkdir -p ${COMPOSER_CACHE_DIR:-$HOME/.cache/composer}

# composer install
echo "Performing needed composer installations"
docker run --rm -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD/dist:/app \
  --volume $SCRIPT_COMPOSER_CACHE:/tmp/cache \
  --entrypoint composer -w /app \
  docker-registry.wikimedia.org/releng/composer-package-php74:0.3.0-s7 install --no-dev --no-progress --optimize-autoloader

# composer update (When ALSO_COMPOSER_UPDATE = 1)
if [ "${ALSO_COMPOSER_UPDATE}" = "1" ]; then
    echo "Performing composer update"
    docker run --rm -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD/dist:/app \
      --volume $SCRIPT_COMPOSER_CACHE:/tmp/cache \
      --entrypoint composer -w /app \
      docker-registry.wikimedia.org/releng/composer-package-php74:0.3.0-s7 update --no-dev --no-progress --optimize-autoloader
    cp dist/composer.lock dist-persist/
else
    echo "SKIPPING: composer update (As you didn't request it)"
fi;

# Sometimes composer git clones things rather than using zips.
# so make sure to remove the .git directories so that things get committed correctly.
# https://github.com/wbstack/mediawiki/issues/5
echo "Cleaning up any .git directories in composer packages..."
find ./dist -mindepth 1 -regex '^./dist/vendor/.*/.*/\.git\(/.*\)?' -delete
