( function () {
	/**
	 * A menu option widget that shows the selection state with a checkbox.
	 *
	 * @extends OO.ui.MenuOptionWidget
	 *
	 * @constructor
	 * @param {Object} config Configuration object
	 */
	mw.libs.advancedSearch.ui.ItemMenuOptionWidget = function ( config ) {
		var layout,
			$label = $( '<div>' )
				.addClass( 'mw-advancedSearch-ui-itemMenuOptionWidget-label' );

		config = config || {};
		mw.libs.advancedSearch.ui.ItemMenuOptionWidget.parent.call( this, $.extend( {
			// Override the 'check' icon that OOUI defines
			icon: ''
		}, config ) );

		this.checkboxWidget = new mw.libs.advancedSearch.ui.CheckboxInputWidget( {
			value: config.data
		} );
		$label.append(
			$( '<div>' )
				.addClass( 'mw-advancedSearch-ui-itemMenuOptionWidget-label-title' )
				.append( $( '<bdi>' ).append( this.$label ) )
		);

		layout = new OO.ui.FieldLayout( this.checkboxWidget, {
			label: $label,
			align: 'inline'
		} );

		// HACK: Prevent defaults on 'click' for the label so it
		// doesn't steal the focus away from the input. This means
		// we can continue arrow-movement after we click the label
		// and is consistent with the checkbox *itself* also preventing
		// defaults on 'click' as well.
		layout.$label.on( 'click', false );

		this.$element
			.addClass( 'mw-advancedSearch-ui-itemMenuOptionWidget' )
			.append(
				$( '<div>' )
					.addClass( 'mw-advancedSearch-ui-cell mw-advancedSearch-ui-itemMenuOptionWidget-itemCheckbox' )
					.append( layout.$element )
			);
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.ItemMenuOptionWidget, OO.ui.MenuOptionWidget );
	// prevents a visual jump when selecting a menu option
	mw.libs.advancedSearch.ui.ItemMenuOptionWidget.static.scrollIntoViewOnSelect = false;

}() );
