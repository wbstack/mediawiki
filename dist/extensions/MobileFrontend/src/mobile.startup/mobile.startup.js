const currentPageHTMLParser = require( './currentPageHTMLParser' );
const time = require( './time' );
const LanguageInfo = require( './LanguageInfo' );
const currentPage = require( './currentPage' );
const Drawer = require( './Drawer' );
const CtaDrawer = require( './CtaDrawer' );
const lazyImageLoader = require( './lazyImages/lazyImageLoader' );
const icons = require( './icons' );
const PageHTMLParser = require( './PageHTMLParser' );
const showOnPageReload = require( './showOnPageReload' );
const OverlayManager = require( './OverlayManager' );
const View = require( './View' );
const Overlay = require( './Overlay' );
const references = require( './references/references' );
const search = {
	SearchOverlay: require( './search/SearchOverlay' ),
	SearchGateway: require( './search/SearchGateway' )
};
const promisedView = require( './promisedView' );
const headers = require( './headers' );
const Skin = require( './Skin' );
const mediaViewer = {
	overlay: require( './mediaViewer/overlay' )
};
const languageInfoOverlay = require( './languageOverlay/languageInfoOverlay' );
const languageOverlay = require( './languageOverlay/languageOverlay' );
const amcOutreach = require( './amcOutreach/amcOutreach' );

// Expose chunk to temporary variable which will be deleted and exported via ResourceLoader
// package inside mobile.startup.exports.

/**
 * The main library for accessing MobileFrontend's stable APIs.
 *
 * @module mobile.startup
 */
mw._mobileFrontend = {
	/**
	 * Internal for use inside Minerva only
	 *
	 * @memberof module:mobile.startup
	 * @type module:mobile.startup/AmcOutreach
	 */

	amcOutreach,
	// Internal for use inside GrowthExperiments only.
	overlayHeader: headers.header,
	/**
	 * Internal for use inside Minerva, GrowthExperiments only.
	 * @type module:mobile.startup/Drawer
	 */
	Drawer,
	// Internal for use inside Minerva only.
	CtaDrawer,
	/**
	 * Internal for use inside Minerva, ExternalGuidance and Echo only.
	 *
	 * @memberof module:mobile.startup
	 * @type module:mobile.startup/View
	 */
	View,
	/**
	 * Internal for use inside Minerva, ExternalGuidance,
	 *  GrowthExperiments and Echo only.
	 *
	 * @memberof module:mobile.startup
	 * @type module:mobile.startup/Overlay
	 */
	Overlay,
	/**
	 * Internal for use inside Minerva only.
	 *
	 * @memberof module:mobile.startup
	 * @type module:mobile.startup/PageHTMLParser
	 */
	currentPageHTMLParser,
	/**
	 * Internal for use inside Minerva, ExternalGuidance and Echo only.
	 *
	 * @memberof module:mobile.startup
	 * @return {module:mobile.startup/OverlayManager}
	 */
	getOverlayManager: () => {
		return OverlayManager.getSingleton();
	},
	/**
	 * Internal for use inside Minerva only.
	 * @type module:mobile.startup/Page
	 * @memberof module:mobile.startup
	 */
	currentPage,
	/**
	 * Internal for use inside Minerva only.
	 * @type module:mobile.startup/PageHTMLParser
	 * @memberof module:mobile.startup
	 */
	PageHTMLParser,
	/**
	 * Internal for use inside Minerva only.
	 * @type module:mobile.startup/Icon
	 * @memberof module:mobile.startup
	 */
	spinner: icons.spinner,
	/**
	 * Internal for use inside Minerva only.
	 *
	 * @memberof module:mobile.startup
	 * @type module:mobile.startup/mediaViewer
	 */
	mediaViewer,
	/**
	 * Internal for use inside Minerva only.
	 *
	 * @memberof module:mobile.startup
	 * @type module:mobile.startup/references
	 */
	references,
	/**
	 * Internal for use inside Minerva only.
	 *
	 * @memberof module:mobile.startup
	 * @type module:mobile.startup/search
	 */
	search,
	/**
	 * Internal for use inside Minerva only.
	 *
	 * @memberof module:mobile.startup
	 * @type module:mobile.startup/time
	 */
	time,
	// Internal for use inside Echo, GrowthExperiments only.
	promisedView,
	/**
	 * Loads all images on the page, stable to call.
	 *
	 * @memberof module:mobile.startup
	 * @return {jQuery.Deferred}
	 */
	loadAllImagesInPage: () => {
		return lazyImageLoader.loadImages(
			lazyImageLoader.queryPlaceholders( document.getElementById( 'content' ) )
		);
	},
	/**
	 * Show a notification on page reload, internal for Minerva
	 *
	 * @memberof module:mobile.startup
	 * @param {string} msg
	 * @return {jQuery.Deferred}
	 */
	notifyOnPageReload: ( msg ) => showOnPageReload( msg ),
	/**
	 * Internal for use inside VisualEditor
	 *
	 * @memberof module:mobile.startup
	 * @return {string|undefined}
	 */
	license() {
		const skin = Skin.getSingleton();
		return skin.getLicenseMsg();
	},
	/**
	 * Internal for use inside Minerva. See {@link module:mobile.startup} for access.
	 *
	 * @module mobile.startup/languages
	 */
	/**
	 * Access to language overlays for usage inside Minerva only.
	 *
	 * @memberof module:mobile.startup
	 * @type module:mobile.startup/languages
	 */
	languages: {
		languageOverlay,
		/**
		 * Shows information about suggested languages.
		 *
		 * @memberof module:mobile.startup/languages
		 * @param {mw.Api} api
		 * @param {boolean} showSuggestedLanguage If the suggested languages section
		 * should be rendered.
		 */
		languageInfoOverlay( api, showSuggestedLanguage ) {
			return languageInfoOverlay( new LanguageInfo( api ), showSuggestedLanguage );
		}
	}
};
