#!/usr/bin/env bash
set -e

pecl install xdebug \  
    && docker-php-ext-enable xdebug  
  
echo "zend_extension=xdebug\n\n\  
[xdebug]\n\  
xdebug.mode=develop,debug\n\  
xdebug.client_host=host.minikube.internal\n\  
xdebug.client_port=9003\n\  
" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
