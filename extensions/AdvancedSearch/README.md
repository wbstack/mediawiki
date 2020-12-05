# AdvancedSearch

The [AdvancedSearch extension](https://www.mediawiki.org/wiki/Extension:AdvancedSearch) enhances
Special:Search by providing an advanced parameters form and improving how namespaces for a
search query are selected.

## Dependencies

This is a mediawiki extension.
Consequently its functionality is tested in integration with a mediawiki installation and the global libraries it provides.
The dependencies in `package.json` try to mimic up-to-date versions of these dependencies for e.g. IDE support, but will not
actually be obeyed when using AdvancedSearch in a wiki.

## Configuration

For configuration options please see the [settings documentation](docs/settings.md).

## Adding More Fields

Please see the "[Adding Fields to AdvancedSearch](docs/adding_fields.md)" documentation to learn how you can add new fields for other search keywords to AdvancedSearch.


## Development

This project uses [npm](https://docs.npmjs.com/) and [grunt](https://gruntjs.com/) to run
JavaScript-related tasks (e.g. linting).
[Docker](https://www.docker.com/) and [docker-compose](https://docs.docker.com/compose/)
_can_ be used to ease installation.

### Installation

    docker-compose run --rm js-build npm install

### Run Linting

    docker-compose run --rm js-build grunt

### Running the QUnit tests

Run MediaWiki and then hit this page in your browser:

    index.php?title=Special%3AJavaScriptTest%2Fqunit%2Fplain&filter=ext.advancedSearch