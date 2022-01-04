( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	/**
	 * @mixin
	 * @requires {OO.ui.DropdownInputWidget}
	 * @constructor
	 *
	 * @param {string} className
	 */
	mw.libs.advancedSearch.ui.ClassesForDropdownOptions = function () {};

	mw.libs.advancedSearch.ui.ClassesForDropdownOptions.prototype.setOptionsData = function ( options ) {
		var
			optionWidgets,
			widget = this;
		this.optionsDirty = true;

		optionWidgets = options.map( function ( opt ) {
			var optValue, optionWidget;

			if ( opt.optgroup === undefined ) {
				optValue = widget.cleanUpValue( opt.data );
				// The following classes are used here:
				// * mw-advancedSearch-inlanguage-*
				// * mw-advancedSearch-filetype-*
				// * mw-advancedSearch-sort-*
				optionWidget = new OO.ui.MenuOptionWidget( {
					data: optValue,
					classes: [ widget.className + optValue.replace( /\W+/g, '-' ) ],
					label: opt.label !== undefined ? opt.label : optValue
				} );
			} else {
				optionWidget = new OO.ui.MenuSectionOptionWidget( {
					label: opt.optgroup,
					classes: []
				} );
			}

			return optionWidget;
		} );

		this.dropdownWidget.getMenu().clearItems().addItems( optionWidgets );
	};

}() );
