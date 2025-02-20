module.exports = {
	root: true,
	extends: [
		'wikimedia/client',
		'wikimedia/jquery',
		'wikimedia/mediawiki'
	],
	globals: {
		dataTypes: 'readonly',
		dataValues: 'readonly',
		util: 'readonly',
		valueFormatters: 'readonly',
		valueParsers: 'readonly',
		wikibase: 'readonly'
	},
	rules: {
		indent: [
			'error',
			'tab',
			{
				SwitchCase: 1,
				MemberExpression: 'off'
			}
		],
		'jsdoc/valid-types': 'off',
		'jsdoc/no-undefined-types': 'off',
		'jsdoc/check-tag-names': 'off',
		'jsdoc/require-returns': 'off',
		'jsdoc/require-returns-check': 'off',
		'jsdoc/require-param': 'off',
		'jsdoc/require-param-type': 'off',
		'max-len': 'off',
		'mediawiki/valid-package-file-require': 'error',
		'new-cap': 'off',
		'no-underscore-dangle': 'off',
		'no-unused-vars': [
			'error',
			{
				args: 'none'
			}
		],
		'no-useless-concat': 'off',
		'no-var': 'off',
		'operator-linebreak': 'off',
		'mediawiki/class-doc': 'off',
		'no-jquery/no-class-state': 'off',
		'no-jquery/no-global-selector': 'off',
		'yml/no-empty-mapping-value': 'off',
		'yml/plain-scalar': 'off',
		'es-x/no-array-string-prototype-at': 'off',
		'es-x/no-async-functions': 'off'
	},
	settings: {
		'no-jquery': {
			variablePattern: '^_?\\$.|^element$'
		},
		jsdoc: {
			preferredTypes: {
				datamodel: 'datamodel',
				EntityIdHtmlFormatter: 'EntityIdHtmlFormatter',
				EntityIdPlainFormatter: 'EntityIdPlainFormatter',
				EntityStore: 'EntityStore',
				EventSingletonManager: 'EventSingletonManager',
				PropertyDataTypeStore: 'PropertyDataTypeStore',
				ValueViewBuilder: 'ValueViewBuilder',
				Variation: 'Variation',
				ViewState: 'ViewState'
			}
		}
	}
};
