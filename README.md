> ℹ️ Issues for this repository are tracked on [Phabricator](https://phabricator.wikimedia.org/project/board/5563/) - ([Click here to open a new one](https://phabricator.wikimedia.org/maniphest/task/edit/form/1/?tags=wikibase_cloud,wbstack_mediawiki
))
# WBStack MediaWiki modifications

The purposes and features of this repository are:
- Update the references to code you want to build MediaWiki from
- Download the code you want to build MediaWiki from
- Store and then copy in the custom WBStack code
- Adjust some MediaWiki code to load custom WBStack code
- Build the Docker image from the code in the `dist` directory
- Test the image
- Build the image automatically on Github Actions platform then push it

This ultimately repackages MediaWiki together with its extensions, skins, and the WBStack code into `dist` and `dist-persist` folders, then creates a new "application" with a much tighter external interface (particularly around configuration).

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

This part describes the Docker image that this repo is designed to build. People, who are only interested in using the image rather than changing/adjusting it, may find this useful.

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
- `MW_ELASTICSEARCH_HOST`: DEPRECATED: elasticsearch hostname. If set "default" settings below are ignored.
- `MW_ELASTICSEARCH_PORT`: DEPRECATED: elasticsearch port
- `MW_DEFAULT_ELASTICSEARCH_HOST`: default (i.e. where data is served to users from) elasticsearch hostname
- `MW_DEFAULT_ELASTICSEARCH_PORT`: default elasticsearch port
- `MW_DEFAULT_ELASTICSEARCH_ES6`: use ES6 compatibility layer with default cluster. Set to "true" or leave unset
- `MW_WRITE_ONLY_ELASTICSEARCH_HOST`: secondary elasticsearch hostname (optional)
- `MW_WRITE_ONLY_ELASTICSEARCH_PORT`: secondary elasticsearch port (optional)
- `MW_WRITE_ONLY_ELASTICSEARCH_ES6`: use ES6 compatibility layer with secondary cluster. Set to "true" or leave unset (optional)
- `MW_LOG_TO_STDERR`: set to "yes" to redirect all mediawiki logging to stderr (so it ends up in the kubernetes pod logs)
- `MW_ALLOWED_PROXY_CIDR`: CIDR block where any IP address in the range is considered a proxy server

## Dockerfile build arguments

- `LOCALIZATION_CACHE_THREAD_COUNT`: Number of threads to use when rebuildLocalisationCache.php runs, defaults to 1
- `LOCALIZATION_CACHE_ADDITIONAL_PARAMS` Additional parameters to pass to rebuildLocalisationCache.php, can for example be used to reduce the number of languages built.
- `INSTALL_XDEBUG`: Installs and configures the php extension xdebug

## Instructions

These documents below will explain how the docker image is built:

- [Build scripts](./docs/build-scripts.md): How `dist` directory is synced with `pacman` and `wikiman`
- [How MediaWiki loads WBStack custom code](./docs/mediawiki-loading.md): How WBStack custom code is loaded into MediaWiki via `LocalSetting.php` or entry point shims
- [Development Environment](./docs/dev-environment.md): How to use this repo on your local development Environment
