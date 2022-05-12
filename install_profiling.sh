#!/usr/bin/env bash
set -e

cd /tmp && \
	git clone "https://github.com/tideways/php-xhprof-extension.git" && \
	cd php-xhprof-extension && \
	phpize && \
	./configure && \
	make && \
	make install && \
	rm -rf /tmp/php-xhprof-extension && \
	echo "extension=tideways_xhprof.so" >> /usr/local/etc/php/conf.d/tweaks.ini