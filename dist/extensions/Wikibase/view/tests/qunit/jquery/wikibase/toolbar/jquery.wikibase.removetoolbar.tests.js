/**
 * @license GPL-2.0-or-later
 * @author H. Snater < mediawiki@snater.com >
 */
( function () {
	'use strict';

	QUnit.module( 'jquery.wikibase.removetoolbar', QUnit.newMwEnvironment( {
		teardown: function () {
			$( '.test_removetoolbar' ).each( function () {
				var $removetoolbar = $( this ),
					removetoolbar = $removetoolbar.data( 'removetoolbar' );

				if ( removetoolbar ) {
					removetoolbar.destroy();
				}

				$removetoolbar.remove();
			} );
		}
	} ) );

	/**
	 * @param {Object} [options]
	 * @return {jQuery}
	 */
	function createRemovetoolbar( options ) {
		return $( '<span>' )
			.addClass( 'test_removetoolbar' )
			.removetoolbar( options || {} );
	}

	QUnit.test( 'Create & destroy', function ( assert ) {
		var $removetoolbar = createRemovetoolbar(),
			removetoolbar = $removetoolbar.data( 'removetoolbar' );

		assert.ok(
			removetoolbar instanceof $.wikibase.removetoolbar,
			'Instantiated widget.'
		);

		removetoolbar.destroy();

		assert.notOk(
			$removetoolbar.data( 'removetoolbar' ),
			'Destroyed widget.'
		);
	} );

}() );
