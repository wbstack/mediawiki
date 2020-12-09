# wbstack MediaWiki overrides

This directory contains the code around MediaWiki to make it better for the wbstack usecase.

This ultimately repackages MediaWiki, extensions, skins, and the wbstack code into a new "application" with a much tighter external interface (particulary around configuration).

This application reaches out to some API (currently the wbstack api) to get the "details" of wikis to be run based on a domain.

This URL is currently always:

```php
'http://' . getenv( 'PLATFORM_API_BACKEND_HOST' ) . '/backend/wiki/getWikiForDomain?domain=' . urlencode($requestDomain);
```

This must respond with a format that looks like the JSON in maintWikWiki.json (which is currently used during Dockerfile building)

This response is then used to configure MediaWiki.

Currently 2 other backend APIs are also included:

- One allows external services to make mediawiki run update.php
- One allows external services to request OAuth consumers

That is all..

## Build scripts

### sync.sh

This script will resync the WHOLE git repo based on repo hashes that are maintained in the sync/fetch.sh file.
Also other things, see the script itself for details.

Gotchas: Must be run from the "mediawiki" directory.

### sync/updateFetchScript

This script can be used to update lots (but not all) of the component hashes to the latest versions.

TODO decide if this is even needed any more since we commit everything in git instead of just building in a Dockerfile...

## MediaWiki Files

MediaWiki loads some files directly from this directory.

Other PHP files are all loaded from within one of these main files.

- LocalSettings.php - Is loaded from the MediaWiki LocalSettings.php
  - WikWikiSpi.php
  - WikWikiLogger.php
  - FinalSettings.php
    - internal/load.php - Only loaded for the BACKEND flavour of MediaWiki
- EntryShim* - These files are loaded at the start of the MediaWiki entry points
  - WikWiki.php - Main code for fetching things from the platform API
