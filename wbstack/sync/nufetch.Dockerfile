FROM php:7.4-cli

RUN apt-get update && apt-get install -y \
        libzip-dev \
        zip \
  && docker-php-ext-install zip \
  && rm -rf /var/lib/apt/lists/*
