/**
 * @module MediaWiki-Popups Integration
 */

/**
 * This function provides a mw.popups object which can be used by 3rd party
 * to interact with Popups. Currently it allows only to read isEnabled flag.
 *
 * @param {Redux.Store} store Popups store
 * @return {Object} external Popups interface
 */
export default function createMwPopups( store ) {
	return {
		// FIXME: This is underspecified. It's meant to be for PagePreviews, but might be false when
		// another or all popup types are disabled.
		isEnabled: function isEnabled() {
			return store.getState().preview.enabled;
		}
	};

}
