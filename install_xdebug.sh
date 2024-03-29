#!/usr/bin/env bash
set -e

pecl install xdebug-3.1.6 \
    && docker-php-ext-enable xdebug  
  
echo "zend_extension=xdebug
[xdebug]
xdebug.mode=develop,debug
xdebug.client_host=host.minikube.internal
xdebug.client_port=9003
xdebug.start_with_request=yes
" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
