## Build scripts

### sync.sh

This script will resync the `dist` directory.

This is made up of a series of steps:

- `pacman`: Retrieive and build MediaWiki code, remove any not needed files
- `sync.sh`: Sync the `dist` directory in the correct way
- `04-docker-compose`: Perform a composer install
- `05-docker-entrypoint-overrides`: Add the WBStack shims to MediaWiki entrypoints

### wikiman & pacman

`wikiman` is a MediaWiki specific yaml generator for pacman.
This needs to be run by developers when updating component versions in `wikiman.yaml`

`pacman` is a generic tool using yaml to fetch a series of codebases and place them on disk.
This is run as a step in `sync.sh`
