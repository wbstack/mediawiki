# WBStack MediaWiki modifications

This directory contains the code around MediaWiki to make it work for the WBStack usecase.

This ultimately repackages MediaWiki, extensions, skins, and the WBStack code into a new "application" with a much tighter external interface (particularly around configuration).

This application reaches out to some API (currently the WBStack api) to get the "wiki info" for a given domain.

This request currently always goes to:

```php
getenv( 'PLATFORM_API_BACKEND_HOST' ) . '/backend/wiki/getWikiForDomain?domain=' . urlencode($requestDomain);
```

This must respond with a format that looks like the JSON in `data/WikiInfo-maint.json` (which is currently used during Dockerfile building)

This response is then used to configure MediaWiki.

An internal flavour of this application also exists that loads some internal only API modules.
These can be found in the `src/Internal` directory.

## Environment variables

- `MW_DB_SERVER_MASTER`: points to a writable mysql service
- `MW_DB_SERVER_REPLICA`: points to a readable mysql service
- `MW_REDIS_SERVER_WRITE`: points to a writable redis service
- `MW_REDIS_SERVER_READ`: points to a readable redis service
- `MW_REDIS_PASSWORD`
- `MW_MAILGUN_API_KEY`
- `MW_MAILGUN_DOMAIN`
- `MW_EMAIL_DOMAIN`
- `MW_RECAPTCHA_SITEKEY`
- `MW_RECAPTCHA_SECRETKEY`
- `PLATFORM_API_BACKEND_HOST`: points to an internal mode wbstack api service
- `MW_ELASTICSEARCH_HOST`: elasticsearch hostname
- `MW_ELASTICSEARCH_PORT`: elasticsearch port
- `MW_LOG_TO_STDERR`: set to "yes" to redirect all mediawiki logging to stderr (so it ends up in the kubernetes pod logs)

## Build scripts

### sync/updateFetchScript.sh

This script can be used to update lots (but not all) of the component hashes in `02-fetch.sh` to the latest versions for the branch.

Gotchas: Must be run from the "sync" directory.

TODO decide if this is even needed any more since we commit everything in git instead of just building in a Dockerfile...

### sync.sh

This script will resync the WHOLE git repo based on repo hashes that are maintained in the sync/02-fetch.sh file.

## MediaWiki Loading

MediaWiki loads this code in 2 ways.

Firstly through entry point shims:

- **(index|load|api|rest).php** - MediaWiki entry points
  - src/Shim/*.php - These files are loaded at the start of the MediaWiki entry points
    - src/loadShim.php
      - src/Info/WBStackInfo.php - Main code for fetching things from the platform API
      - src/Logging/WikWikiSpi.php
      - src/Logging/WikWikiLogger.php

And secondly via LocalSettings.php

- **LocalSettings.php** - The actual MediaWiki settings file
  - src/Settings/LocalSettings.php - Is loaded from the MediaWiki LocalSettings.php (where it normally would be)
    - src/loadAll.php
    - src/Settings/Cache.php
    - src/loadInternal.php - Only loaded for the INTERNAL flavour of the app.
      - src/Internal/*
    - src/Settings/Hooks.php

## Secondary setup

### ElasticSearch index configuration

In order to enable elasticsearch the `UpdateSearchIndexConfig.php` needs to be executed for that wiki.
On wiki creation through the API this is done by the `ApiWbStackElasticSearchInit` job.

## Development Environment

There is a [docker-compose file](../docker-compose.yml) in the root directory that allows for serving multiple development sites locally.

These are currently not using the real api but rather gets their settings from the static json files included in the data folder.

The fake api is served by the [server.php](test/server.php) script and reads the corresponding [subdomain](data/WikiInfo-site1.json) from each request.


### Start the dev environment

```sh
docker-compose up
```

Wait until both sites are accessible:

 - http://site1.localhost:8001/wiki/Main_Page
 - http://site2.localhost:8001/wiki/Main_Page

 You may need to add an entry to your `hosts` file:

 ```
 127.0.0.1 site1.localhost site2.localhost
 ```

 Once the sites are accessible you can perform secondary setup (_The request takes a while to execute_):

 ```sh
curl -l -X POST "http://site1.localhost:8001/w/api.php?action=wbstackElasticSearchInit&format=json"
curl -l -X POST "http://site2.localhost:8001/w/api.php?action=wbstackElasticSearchInit&format=json"
```

### Debugging Elastic

General overview of the cluster

```
http://localhost:9200/_stats
```

Entries in the content index (Items, Lexemes) for `site1.localhost` can be found by going to the following url

```
http://localhost:9200/site1.localhost_content_first/_search
```
