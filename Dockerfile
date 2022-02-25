FROM php:7.4-apache

# This file is mostly copied from the community mediawiki-docker image
# Just with the actual mediawiki install etc removed

# System dependencies
RUN set -eux; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		# in mediawiki-docker image
		git \
		librsvg2-bin \
		imagemagick \
		# Required for SyntaxHighlighting
		python3 \
		# Requires for Score
		lilypond \
	; \
	rm -rf /var/lib/apt/lists/*

# Install the PHP extensions we need
RUN set -eux; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		libicu-dev \
		# Needed for mbstring php ext (from PHP 7.4)
		libonig-dev \
		# Needed for bz2 php ext
		libbz2-dev \
	; \
	\
	docker-php-ext-install -j "$(nproc)" \
		# in mediawiki-docker image
		intl \
		mbstring \
		mysqli \
		opcache \
		# calendar https://github.com/addshore/wbstack/issues/36
		calendar \
		# Scribunto pcre, pcntl, mbstring
		pcntl \
		# Needed for some dump formats
		bz2 \
	; \
	\
	pecl install apcu-5.1.20; \
	# redis added for wbstack
	pecl install redis-5.3.4; \
	docker-php-ext-enable \
		apcu \
		redis \
	; \
	rm -r /tmp/pear; \
	\
	# reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark; \
	ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
		| awk '/=>/ { print $3 }' \
		| sort -u \
		| xargs -r dpkg-query -S \
		| cut -d: -f1 \
		| sort -u \
		| xargs -rt apt-mark manual; \
	\
	apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
	rm -rf /var/lib/apt/lists/*

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=60'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Tweak other PHP.ini settings
RUN { \
		echo 'memory_limit = 256M'; \
	} > /usr/local/etc/php/conf.d/tweaks.ini

RUN set -eux; \
	a2enmod rewrite; \
	{ \
		echo '<Directory /var/www/html>'; \
		echo '  RewriteEngine On'; \
		# Enable Short URLs
		echo '  RewriteRule ^/*$ %{DOCUMENT_ROOT}/w/index.php [L]'; \
		echo '  RewriteRule ^/?wiki(/.*)?$ %{DOCUMENT_ROOT}/w/index.php [L]'; \
		# Enable Wikibase /entity/ redirects, per https://meta.wikimedia.org/wiki/Wikidata/Notes/URI_scheme
		echo '  RewriteRule ^/?entity/(.*)$ /wiki/Special:EntityData/$1 [R=303,QSA]'; \
		echo '</Directory>'; \
	} > "$APACHE_CONFDIR/conf-available/mediawiki.conf"; \
	a2enconf mediawiki

# Copy the code!
COPY --chown=www-data:www-data ./dist/ /var/www/html/w

# Generate localization cache files
# TODO could run mediawiki builds on a bigger machine and make use of more threads
# TODO it would be much better to ADD / COPY files after this? urgff.
# or cache the output of the cache rebuild and then try to grab that during buildss!!! :D
RUN WBS_DOMAIN=maint php ./w/maintenance/rebuildLocalisationCache.php

ENV MW_WIKIBASE_CONCEPTURI_SCHEME='http://'

LABEL org.opencontainers.image.source="https://github.com/wbstack/mediawiki"
