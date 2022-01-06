## Build scripts

### wikiman & pacman

`wikiman` is a MediaWiki specific yaml generator for pacman.
This needs to be run by developers when updating component versions in `wikiman.yaml`

`pacman` is a generic tool using yaml to fetch a series of codebases and place them on disk.
This is run as a step in `sync.sh`

### sync.sh

This script will resync the WHOLE git repo.

This is made up of a series of steps:

- `01-clear`: Remove all MediaWiki code
- `pacman`: Retrieive and build MediaWiki code
- `03-less-files`: Remove not needed things from MediaWiki code
- `04-docker-compose`: Perform a composer install
- `05-docker-entrypoint-overrides`: Add the WBStack shims to MediaWiki entrypoints
