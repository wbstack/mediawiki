#!/usr/bin/env bash
set -e

pecl install xdebug \
    && docker-php-ext-enable xdebug  
  
echo "zend_extension=xdebug
[xdebug]
xdebug.mode=develop,debug
xdebug.client_host=host.minikube.internal
xdebug.client_port=9003
" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
