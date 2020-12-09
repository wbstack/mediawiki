#!/usr/bin/env sh

echo "Performing needed composer installations"

SCRIPT_COMPOSER_CACHE=${COMPOSER_CACHE_DIR:-$HOME/.cache/composer}

mkdir -p ${COMPOSER_CACHE_DIR:-$HOME/.cache/composer}

# composer install
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/app \
  --volume $SCRIPT_COMPOSER_CACHE:/tmp/cache \
composer:1 install --no-dev --no-progress --optimize-autoloader

# composer update
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/app \
  --volume $SCRIPT_COMPOSER_CACHE:/tmp/cache \
composer:1 update --no-dev --no-progress --optimize-autoloader

# Per the Mailgun docs this is need, but it would be nicer to fix this
# TODO don't require this, make it user composer merge plugin as everything else does
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD/extensions/Mailgun:/app \
  --volume $SCRIPT_COMPOSER_CACHE:/tmp/cache \
composer:1 update --no-dev --no-progress --optimize-autoloader