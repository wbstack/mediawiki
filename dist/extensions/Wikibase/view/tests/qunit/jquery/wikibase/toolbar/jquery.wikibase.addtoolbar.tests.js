/**
 * @license GPL-2.0-or-later
 * @author H. Snater < mediawiki@snater.com >
 */
( function () {
	'use strict';

	QUnit.module( 'jquery.wikibase.addtoolbar', QUnit.newMwEnvironment( {
		teardown: function () {
			$( '.test_addtoolbar' ).each( function () {
				var $addtoolbar = $( this ),
					addtoolbar = $addtoolbar.data( 'addtoolbar' );

				if ( addtoolbar ) {
					addtoolbar.destroy();
				}

				$addtoolbar.remove();
			} );
		}
	} ) );

	/**
	 * @param {Object} [options]
	 * @return {jQuery}
	 */
	function createAddtoolbar( options ) {
		return $( '<span>' )
			.addClass( 'test_addtoolbar' )
			.addtoolbar( options || {} );
	}

	QUnit.test( 'Create & destroy', function ( assert ) {
		var $addtoolbar = createAddtoolbar(),
			addtoolbar = $addtoolbar.data( 'addtoolbar' );

		assert.ok(
			addtoolbar instanceof $.wikibase.addtoolbar,
			'Instantiated widget.'
		);

		addtoolbar.destroy();

		assert.notOk(
			$addtoolbar.data( 'addtoolbar' ),
			'Destroyed widget.'
		);
	} );

}() );
