This file provides documentation for AdvancedSearch configuration variables.

It should be updated each time a new configuration parameter is added or changed.

## Configuration

### Namespace presets `$wgAdvancedSearchNamespacePresets`

AdvancedSearch supports namespace presets, groups of namespaces that are offered for batch selection via dedicated checkboxes. [By default](https://phabricator.wikimedia.org/diffusion/EASR/browse/master/extension.json$32) the following presets are offered: _defaultNamespaces_, _discussion_, _generalHelp_, and _all_.

Which namespaces are contained in a preset can be configured
* statically, through the `namespaces` key containing an array of namespace ids,
* or programmatically through the `provider` key containing a reference to a JavaScript function returning the aforementioned namespace id array. The available provider functions are implemented in [NamespacePresetProviders](https://phabricator.wikimedia.org/diffusion/EASR/browse/master/modules/dm/ext.advancedSearch.NamespacePresetProviders.js).

You can use `$wgAdvancedSearchNamespacePresets` to modify the default configuration or add your own presets.

#### Add a namespace preset
```
// in your LocalSettings.php
wfLoadExtension( 'AdvancedSearch' );
$wgAdvancedSearchNamespacePresets = [
	'my-custom-preset' => [
		'enabled' => true, // indication that this preset should be shown to the user
		'namespaces' => [ '1', '11' ], // list of namespaces to include in this preset
		'label' => 'my-custom-preset-label-id' // id of the translation to use to label the preset checkbox
	],
];
```

#### Disable a default namespace preset
```
// in your LocalSettings.php
wfLoadExtension( 'AdvancedSearch' );
$wgAdvancedSearchNamespacePresets = [
	'generalHelp' => [
		'enabled' => false,
	],
];
```

#### Add a dynamic namespace preset
If your wiki needs to determine namespaces at runtime or if you write an extension that can provide a dynamic namespace preset, you can use the `provider` setting instead of the `namespaces` setting.

```
// in your LocalSettings.php
wfLoadExtension( 'AdvancedSearch' );
$wgAdvancedSearchNamespacePresets = [
	'my-custom-preset' => [
		'enabled' => true, // indication that this preset should be shown to the user
		'provider' => 'custom-talk', // unique provider id
		'label' => 'my-custom-preset-label-id' // message id of the translation to use as a label for the preset checkbox
	]
];
```

```
// in the Javascript initialization code of your extension or in the common.js of your wiki
function customTalkNamespaceProvider( namespaceIds ) {
	$.grep( namespaceIds, function ( id ) {
		var numericId = Number( id );
		return numericId > 100 && numericId % 2;
	} );
}

mw.hook( 'advancedSearch.initNamespacePresetProviders' ).add(
	function( namespaceProviders ) {
		// use unique provider name from PHP config as key
		namespaceProviders[ 'custom-talk' ] = customTalkNamespaceProvider;
	}
);
```

The provider function `customTalkNamespaceProvider` will get an array of all supported namespaces
ids. If it returns unsupported namespace ids, the preset will not be shown.
If the provider function returns an empty array, the preset is not shown. This is for creating presets that depend on the existence of certain namespaces.

### Category tree support `$wgAdvancedSearchDeepcatEnabled`

AdvancedSearch acts as a remote for [CirrusSearch](https://www.mediawiki.org/wiki/Help:CirrusSearch) features. An advanced, optional CirrusSearch feature is `deepcat:`, which allows to search in subcategories. By default, AdvancedSearch assumes this is available. It needs to be disabled on wikis that can't or don't want to set up the [required SPARQL service](https://www.mediawiki.org/wiki/Help:CirrusSearch#Deepcategory).

```
// in your LocalSettings.php
$wgAdvancedSearchDeepcatEnabled = false; // disable deepcat: in favor of incategory:
```
