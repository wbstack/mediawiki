module.exports = ( function ( QUnit, wb ) {
	'use strict';

	return {
		all: function ( constructor, getInstance ) {
			this.constructorTests( constructor, getInstance );
			this.methodTests( getInstance );
		},

		constructorTests: function ( constructor, getInstance ) {
			QUnit.test( 'implements wb.view.ViewController', function ( assert ) {
				var controller = getInstance();

				assert.ok( controller instanceof constructor );
			} );
		},

		methodTests: function ( getInstance ) {
			QUnit.test( 'has non-abstract startEditing method', function ( assert ) {
				var controller = getInstance();

				controller.startEditing();

				assert.ok( true );
			} );

			QUnit.test( 'has non-abstract stopEditing method', function ( assert ) {
				var controller = getInstance();

				controller.stopEditing();

				assert.ok( true );
			} );

			QUnit.test( 'has non-abstract cancelEditing method', function ( assert ) {
				var controller = getInstance();

				controller.cancelEditing();

				assert.ok( true );
			} );

			QUnit.test( 'has non-abstract setError method', function ( assert ) {
				var controller = getInstance();

				controller.setError();

				assert.ok( true );
			} );

			QUnit.test( 'has non-abstract remove method', function ( assert ) {
				var controller = getInstance();

				controller.remove();

				assert.ok( true );
			} );
		}

	};

}( QUnit, wikibase ) );
