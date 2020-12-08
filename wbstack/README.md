# wbstack MediaWiki overrides

This directory contains the hacks around MediaWiki to make it better for the wbstack usecase.

It's currently quite a big mess of files and should probably be split into directories..

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
    - InternalSettings.php - Only loaded for the BACKEND flavour of MediaWiki
- EntryShim* - These files are loaded at the start of the MediaWiki entry points
  - WikWiki.php - Main code for fetching things from the platform API
