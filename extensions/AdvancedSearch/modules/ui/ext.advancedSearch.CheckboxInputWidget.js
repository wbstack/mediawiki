( function () {
	/**
	 * A widget representing a single toggle filter
	 *
	 * @extends OO.ui.CheckboxInputWidget
	 *
	 * @constructor
	 * @param {Object} config Configuration object
	 */
	mw.libs.advancedSearch.ui.CheckboxInputWidget = function ( config ) {
		config = config || {};

		mw.libs.advancedSearch.ui.CheckboxInputWidget.parent.call( this, config );
		// This checkbox is fake and used only for visual purposes.
		// Event handling is done for the entire menu item element in mw.libs.advancedSearch.ui.NamespaceFilters
		this.$input
			.on( 'click', false );
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.CheckboxInputWidget, OO.ui.CheckboxInputWidget );

}() );
