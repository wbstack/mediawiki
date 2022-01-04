/**
 * @license GPL-2.0-or-later
 * @author H. Snater < mediawiki@snater.com >
 */
( function () {
	'use strict';

	/**
	 * @param {Object} [options]
	 * @return {jQuery}
	 */
	var createTestItem = function ( options ) {
		return $( '<span>' )
			.text( 'text' )
			.addClass( 'test_toolbaritem' )
			.toolbaritem( options || {} );
	};

	QUnit.module( 'jquery.wikibase.toolbaritem', QUnit.newMwEnvironment( {
		teardown: function () {
			$( '.test_toolbaritem' ).each( function () {
				var $item = $( this ).data( 'toolbaritem' ),
					item = $item.data( 'toolbaritem' );

				if ( item ) {
					item.destroy();
				}

				$item.remove();
			} );
		}
	} ) );

	QUnit.test( 'Create & destroy', function ( assert ) {
		var $item = createTestItem(),
			item = $item.data( 'toolbaritem' );

		assert.ok(
			item instanceof $.wikibase.toolbaritem,
			'Instantiated widget.'
		);

		item.destroy();

		assert.notOk(
			$item.data( 'toolbaritem' ),
			'Destroyed widget.'
		);
	} );

}() );
