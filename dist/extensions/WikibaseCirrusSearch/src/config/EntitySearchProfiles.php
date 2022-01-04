<?php
// Search profiles for fulltext search
// Matches the syntax of Cirrus search profiles, e.g. in FullTextQueryBuilderProfiles.config.php
// Note that these will be merged with Cirrus standard profiles,
// so prefixing with 'wikibase' is recommended.
return [
	'wikibase' => [
		'builder_factory' => [ \Wikibase\Search\Elastic\EntityFullTextQueryBuilder::class, 'newFromGlobals' ],
		'settings' => [
			'any'               => 0.04,
			'lang-exact'        => 0.78,
			'lang-folded'       => 0.01,
			'lang-partial'      => 0.07,
			'fallback-exact'    => 0.38,
			'fallback-folded'   => 0.005,
			'fallback-partial'  => 0.03,
			'fallback-discount' => 0.1,
			'phrase' => [
				'all'           => 0.001,
				'all.plain'     => 0.01,
				'slop'          => 0,
			],
		]
	],
];
