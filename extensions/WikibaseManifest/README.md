# WikibaseManifest

WikibaseManifest is an extension that combines metadata about a Wikibase installation exposing it as a simple API.
The goal is to help toolmakers write tools that can target any Wikibase.

The manifest will be exposed at an endpoint such as https://www.wikidata.org/w/rest.php/wikibase/manifest/v0/manifest

More about the format of the manifest and how it came to be can be found [here](/docs/manifest_output_format.md).

### Installation

In order to use this extension you need to load the extension.

```php
wfLoadExtension( 'WikibaseManifest' );
```

Then configure as appropriate:

```php
$wgWbManifestExternalServiceMapping = [
	'queryservice_ui' => 'https://query.wikidata.org',
];
$wgWbManifestWikidataEntityMapping = [
	'properties' => [
		'P31' => 'P1',
	],
	'items' => [
		'Q5' => 'Q15'
	],
];
$wgWbManifestMaxLag = 7;
```

### Development

We recommend using `mediawiki-docker-dev` for development.

You can test the development endpoint at:
http://default.web.mw.localhost:8080/mediawiki/rest.php/wikibase/manifest/v0/manifest

**PHP**

You can run the code linting with:
```sh
composer test
```

Then fix errors with:
```sh
composer fix
```

To run phpunit tests with mediawiki-docker-dev run:
```sh
mw-docker-dev phpunit-file default extensions/WikibaseManifest/tests/phpunit/
```

**JS**

You can run the api end-to-end tests using:
```sh
npm run api-testing
```

You can run the api end-to-end test linting with:
```sh
npm run api-testing-lint
```

Then fix errors with:
```sh
npm run api-testing-lint -- --fix
```
