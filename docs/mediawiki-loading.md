## MediaWiki Loading

MediaWiki loads WBStack custom code in 2 ways.

Firstly through entry point shims:

- **(index|load|api|rest).php** - MediaWiki entry points
  - `src/Shim/*.php` - These files are loaded at the start of the MediaWiki entry points
    - `src/loadShim.php`
      - `src/Info/WBStackInfo.php` - Main code for fetching things from the platform API
      - `src/Logging/WikWikiSpi.php`
      - `src/Logging/WikWikiLogger.php`

And secondly via LocalSettings.php

- **LocalSettings.php** - The actual MediaWiki settings file
  - `src/Settings/LocalSettings.php` - Is loaded from the MediaWiki LocalSettings.php (where it normally would be)
    - `src/loadAll.php`
    - `src/Settings/Cache.php`
    - `src/loadInternal.php` - Only loaded for the INTERNAL flavour of the app.
      - `src/Internal/*`
    - `src/Settings/Hooks.php`
