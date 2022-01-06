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

- [Environment variables](./docs/environment-vars.md)
- [Build scripts](./docs/build-scripts.md)  
- [MediaWiki Loading](./docs/mediawiki-loading.md)
- [Secondary setup](./docs/secondary-setup.md)
- [Development Environment](./docs/dev-environment.md)