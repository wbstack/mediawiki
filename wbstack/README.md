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

## Build scripts

### sync.sh

This script will resync the WHOLE git repo based on repo hashes that are maintained in the sync/fetch.sh file.
Also other things, see the script itself for details.

Gotchas: Must be run from the "mediawiki" directory.

### sync/updateFetchScript.sh

This script can be used to update lots (but not all) of the component hashes to the latest versions.

TODO decide if this is even needed any more since we commit everything in git instead of just building in a Dockerfile...

Gotchas: Must be run from the "sync" directory.

## MediaWiki Files

MediaWiki loads some files directly from this directory.

Other PHP files are all loaded from within one of these main files.

- EntryShim* - These files are loaded at the start of the MediaWiki entry points
  - src/load.php
    - src/Info/WBStackInfo.php - Main code for fetching things from the platform API
    - src/Logging/WikWikiSpi.php
    - src/Logging/WikWikiLogger.php
- LocalSettings.php - Is loaded from the MediaWiki LocalSettings.php (where it normally would be)
  - FinalSettings.php
    - src/loadInternal.php - Only loaded for the INTERNAL flavour of the app.
      - src/Internal/*