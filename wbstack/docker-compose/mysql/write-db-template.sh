#!/bin/bash
set -ex

# Splits the $MYSQL_PREFIXS and $MYSQL_DATABASES into arrays and replaces writes the
# schema into the initialization folder

IFS=','
read -r -a PREFIX_ARRAY <<< "$MYSQL_PREFIXES"
read -r -a DATABASE_ARRAY <<< "$MYSQL_DATABASES"

for index in "${!PREFIX_ARRAY[@]}"
do
    sed "s/<<REPLACE_PREFIX>>/${PREFIX_ARRAY[index]}/g;s/<<REPLACE_DATABASE>>/${DATABASE_ARRAY[index]}/g" \
     /template/schema.sql > "/docker-entrypoint-initdb.d/${index}-${PREFIX_ARRAY[index]}.sql"
done

