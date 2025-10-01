const
	mfExtend = require( './mfExtend' ),
	util = require( './util' ),
	View = require( './View' );

/**
 * @class module:mobile.startup/Icon
 * @classdesc A wrapper for creating an icon.
 * @extends module:mobile.startup/View
 * @param {Object} options Configuration options
 */
function Icon( options ) {
	View.call( this, options );
}

mfExtend( Icon, View, {
	/**
	 * @inheritdoc
	 * @memberof Icon
	 * @instance
	 */
	preRender() {
		this.options._iconClasses = this.getIconClasses();
	},
	/**
	 * Internal method that sets the correct rotation class for the icon
	 * based on the value of rotation
	 *
	 * @memberof Icon
	 * @instance
	 * @private
	 */
	getRotationClass() {
		let rotationClass = '';
		if ( this.options.rotation ) {
			switch ( this.options.rotation ) {
				case -180:
				case 180:
					rotationClass = 'mf-icon-rotate-flip';
					break;
				case -90:
					rotationClass = 'mf-icon-rotate-anti-clockwise';
					break;
				case 90:
					rotationClass = 'mf-icon-rotate-clockwise';
					break;
				case 0:
					break;
				default:
					throw new Error( 'Bad value for rotation given. Must be ±90, 0 or ±180.' );
			}
		}
		return rotationClass;
	},
	/**
	 * Set icon glyph class and icon type class
	 *
	 * @memberof Icon
	 * @instance
	 * @private
	 */
	getIconClasses() {
		const base = this.options.base;
		const icon = this.options.icon;
		const isSmall = this.options.isSmall;
		const rotationClasses = this.getRotationClass();
		const additionalClassNames = this.options.additionalClassNames;

		let classes = base + ' ';
		if ( icon ) {
			classes += this.getGlyphClassName() + ' ';
		}
		if ( isSmall ) {
			classes += 'mf-icon--small ';
		}
		if ( additionalClassNames ) {
			classes += additionalClassNames + ' ';
		}

		return classes + rotationClasses;
	},
	/**
	 * @inheritdoc
	 * @memberof Icon
	 * @instance
	 */
	isTemplateMode: true,
	/**
	 * @memberof Icon
	 * @instance
	 * @mixes module:mobile.startup/View#defaults
	 * @property {Object} defaults
	 * @property {string} defaults.base Base icon class.
	 * Defaults to 'mf-icon'.
	 * @property {string} defaults.glyphPrefix Prefix for the icon class
	 * Defaults to 'mf'.
	 * @property {string} defaults.icon Name of the icon.
	 * @property {boolean} defaults.rotation will rotate the icon by a certain number
	 *  of degrees. Must be ±90, 0 or ±180 or will throw exception.
	 * @property {boolean} defaults.isSmall If icon is small.
	 * @property {string} defaults.addtionalClassNames Additional classes to be added to the icon.
	 */
	defaults: {
		base: 'mf-icon',
		glyphPrefix: null,
		icon: '',
		rotation: 0,
		isSmall: false,
		additionalClassNames: null
	},
	/**
	 * Return the full class name that is required for the icon to render
	 *
	 * @memberof Icon
	 * @instance
	 * @return {string}
	 */
	getClassName() {
		return this.$el.attr( 'class' );
	},
	/**
	 * Return the class that relates to the icon glyph
	 *
	 * @memberof Icon
	 * @instance
	 * @return {string}
	 */
	getGlyphClassName() {
		if ( this.options.glyphPrefix ) {
			return 'mf-icon-' + this.options.glyphPrefix + '-' + this.options.icon;
		}
		return 'mf-icon-' + this.options.icon;
	},

	template: util.template(
		'<span class="{{_iconClasses}}"> </span>'
	)
} );

module.exports = Icon;
