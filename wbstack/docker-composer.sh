docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/app composer:1 install --no-dev --no-progress --optimize-autoloader
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD:/app composer:1 update --no-dev --no-progress --optimize-autoloader
# Per the Mailgun docs this is need, but it would be nicer to fix this
# TODO don't require this, make it user composer merge plugin as everything else does
docker run --rm -it -u $(id -u ${USER}):$(id -g ${USER}) -v $PWD/extensions/Mailgun:/app composer:1 update --no-dev --no-progress --optimize-autoloader