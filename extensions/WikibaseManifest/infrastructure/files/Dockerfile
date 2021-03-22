FROM mediawiki:1.34

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install --yes --no-install-recommends libbz2-dev=1.* gettext-base=0.19.* wget && \
    rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

RUN install -d /var/log/mediawiki -o www-data

RUN docker-php-ext-install calendar bz2

COPY entrypoint.sh /entrypoint.sh
COPY wait-for-it.sh /wait-for-it.sh
COPY extraSettings.php.fragment /extraSettings.php.fragment
RUN chmod +x /wait-for-it.sh

RUN ln -s /var/www/html /var/www/html/wiki

ENTRYPOINT ["/bin/bash"]
CMD ["/entrypoint.sh"]
