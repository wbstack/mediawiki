#!/usr/bin/env sh

echo "Performing needed composer installations"

SCRIPT_COMPOSER_CACHE=${COMPOSER_CACHE_DIR:-$HOME/.cache/composer}

mkdir -p ${COMPOSER_CACHE_DIR:-$HOME/.cache/composer}

# composer install
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/app \
  --volume $SCRIPT_COMPOSER_CACHE:/tmp/cache \
composer@sha256:d374b2e1f715621e9d9929575d6b35b11cf4a6dc237d4a08f2e6d1611f534675 install --no-dev --no-progress --optimize-autoloader

# composer update
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/app \
  --volume $SCRIPT_COMPOSER_CACHE:/tmp/cache \
composer@sha256:d374b2e1f715621e9d9929575d6b35b11cf4a6dc237d4a08f2e6d1611f534675 update --no-dev --no-progress --optimize-autoloader

# Per the Mailgun docs this is need, but it would be nicer to fix this
# TODO don't require this, make it user composer merge plugin as everything else does
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD/extensions/Mailgun:/app \
  --volume $SCRIPT_COMPOSER_CACHE:/tmp/cache \
composer@sha256:d374b2e1f715621e9d9929575d6b35b11cf4a6dc237d4a08f2e6d1611f534675 update --no-dev --no-progress --optimize-autoloader

# Sometimes composer git clones things rather than using zips.
# so make sure to remove the .git directories so that things get committed correctly.
# https://github.com/wbstack/mediawiki/issues/5
echo "Cleaning up any .git directories in composer packages..."
find ./ -mindepth 1 -regex '^./vendor/.*/.*/\.git\(/.*\)?' -delete
