<?php
// Wikibase prefix search scoring profile for CirrusSearch.
// This profile applies to the whole document.
// These configurations define how the results are ordered.
// The names should be distinct from other Cirrus rescoring profile, so
// prefixing with 'wikibase' is recommended.
return [
	'wikibase_prefix' => [
		'i18n_msg' => 'wikibasecirrus-rescore-profile-prefix',
		'supported_namespaces' => 'all',
		'rescore' => [
			[
				'window' => 8192,
				'window_size_override' => 'EntitySearchRescoreWindowSize',
				'query_weight' => 1.0,
				'rescore_query_weight' => 1.0,
				'score_mode' => 'total',
				'type' => 'function_score',
				'function_chain' => 'entity_weight'
			],
		]
	],
	// Profile that uses both entity weight end statement-based boosts
	'wikibase_prefix_boost' => [
		'i18n_msg' => 'wikibasecirrus-rescore-profile-prefix-boost',
		'supported_namespaces' => 'all',
		'rescore' => [
			[
				'window' => 8192,
				'window_size_override' => 'EntitySearchRescoreWindowSize',
				'query_weight' => 1.0,
				'rescore_query_weight' => 1.0,
				'score_mode' => 'total',
				'type' => 'function_score',
				'function_chain' => 'entity_weight_boost'
			],
		]
	],
	// Fulltext profile
	'wikibase' => [
		'i18n_msg' => 'wikibasecirrus-rescore-profile-fulltext',
		'supported_namespaces' => 'all',
		'rescore' => [
			[
				'window' => 8192,
				'window_size_override' => 'EntitySearchRescoreWindowSize',
				'query_weight' => 1.0,
				'rescore_query_weight' => 1.0,
				'score_mode' => 'total',
				'type' => 'function_score',
				'function_chain' => 'entity_weight_boost'
			],
		]
	],
	// Fulltext profile with phrase scoring
	'wikibase_phrase' => [
		'i18n_msg' => 'wikibasecirrus-rescore-profile-fulltext-phrase',
		'supported_namespaces' => 'all',
		'rescore' => [
			// phrase rescore
			[
				'window' => 512,
				'window_size_override' => 'CirrusSearchPhraseRescoreWindowSize',
				'rescore_query_weight' => 10,
				'rescore_query_weight_override' => 'CirrusSearchPhraseRescoreBoost',
				'query_weight' => 1.0,
				'type' => 'phrase',
				// defaults: 'score_mode' => 'total'
			],
			[
				'window' => 8192,
				'window_size_override' => 'EntitySearchRescoreWindowSize',
				'query_weight' => 1.0,
				'rescore_query_weight' => 2.0,
				'score_mode' => 'total',
				'type' => 'function_score',
				'function_chain' => 'entity_weight_boost'
			],
		]
	]
];
