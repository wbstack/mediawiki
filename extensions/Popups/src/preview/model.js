/**
 * @module preview/model
 */

/**
 * Page Preview types as defined in Schema:Popups
 * https://meta.wikimedia.org/wiki/Schema:Popups
 *
 * @constant {Object}
 */
const previewTypes = {
	/** Empty preview used in error situations */
	TYPE_GENERIC: 'generic',
	/** Standard page preview with or without thumbnail */
	TYPE_PAGE: 'page',
	/** Disambiguation page preview */
	TYPE_DISAMBIGUATION: 'disambiguation',
	/** Reference preview **/
	TYPE_REFERENCE: 'reference'
};

export { previewTypes };

/**
 * Preview Model
 *
 * @typedef {Object} PreviewModel
 * @property {string} url The canonical URL of the page being previewed
 * @property {string} type One of the previewTypes.TYPE_… constants.
 *
 * @global
 */

/**
 * @typedef {Object} PagePreviewModel
 * @extends PreviewModel
 * @property {string} title
 * @property {Array|undefined} extract `undefined` if the extract isn't
 *  viable, e.g. if it's empty after having ellipsis and parentheticals
 *  removed; this can be used to present default or error states
 * @property {string} languageCode
 * @property {string} languageDirection Either "ltr" or "rtl", or an empty string if undefined.
 * @property {{source: string, width: number, height: number}|undefined} thumbnail
 * @property {number} pageId Currently not used by any known popup type.
 *
 * @global
 */

/**
 * @typedef {Object} ReferencePreviewModel
 * @extends PreviewModel
 * @property {string} extract An HTML snippet, not necessarily with a single top-level node
 * @property {string} referenceType A type identifier, e.g. "web"
 * @property {string} sourceElementId ID of the parent element that triggered the preview
 *
 * @global
 */

/**
 * Creates a page preview model.
 *
 * @param {string} title
 * @param {string} url The canonical URL of the page being previewed
 * @param {string} languageCode
 * @param {string} languageDirection Either "ltr" or "rtl"
 * @param {Array|undefined|null} extract
 * @param {string} type
 * @param {{source: string, width: number, height: number}|undefined} [thumbnail]
 * @param {number} [pageId]
 * @return {PagePreviewModel}
 */
export function createModel(
	title,
	url,
	languageCode,
	languageDirection,
	extract,
	type,
	thumbnail,
	pageId
) {
	const processedExtract = processExtract( extract ),
		previewType = getPagePreviewType( type, processedExtract );

	return {
		title,
		url,
		languageCode,
		languageDirection,
		extract: processedExtract,
		type: previewType,
		thumbnail,
		pageId
	};
}

/**
 * Creates an empty page preview model.
 *
 * @param {string} title
 * @param {string} url
 * @return {PagePreviewModel}
 */
export function createNullModel( title, url ) {
	return createModel( title, url, '', '', [], '' );
}

/**
 * Determines the applicable popup type based on title and link element.
 *
 * @param {Element} el
 * @param {mw.Map} config
 * @param {mw.Title} title
 * @return {string|null}
 */
export function getPreviewType( el, config, title ) {
	if ( !isSelfLink( title, config ) ) {
		return previewTypes.TYPE_PAGE;
	}

	// The other selector can potentially pick up self-links with a class="reference"
	// parent, but no fragment
	if ( title.getFragment() &&
		config.get( 'wgPopupsReferencePreviews' ) &&
		// eslint-disable-next-line no-jquery/no-class-state
		$( el ).parent().hasClass( 'reference' )
	) {
		return previewTypes.TYPE_REFERENCE;
	}

	return null;
}

/**
 * @param {mw.Title} title
 * @param {mw.Map} config
 * @return {boolean} True when the link points to the current page.
 */
function isSelfLink( title, config ) {
	return title.getNamespaceId() === config.get( 'wgNamespaceNumber' ) &&
		title.getMainText() === config.get( 'wgTitle' );
}

/**
 * Processes the extract returned by the TextExtracts MediaWiki API query
 * module.
 *
 * If the extract is `undefined`, `null`, or empty, then `undefined` is
 * returned.
 *
 * @param {Array|undefined|null} extract
 * @return {Array|undefined} Array when extract is an not empty array, undefined
 *  otherwise
 */
function processExtract( extract ) {
	if ( extract === undefined || extract === null || extract.length === 0 ) {
		return undefined;
	}
	return extract;
}

/**
 * Determines the page preview type based on whether or not:
 * a. Is the preview empty.
 * b. The preview type matches one of previewTypes.
 * c. Assume standard page preview if both above are false
 *
 * @param {string} type
 * @param {Array|undefined} [processedExtract]
 * @return {string} One of the previewTypes.TYPE_… constants.
 */

function getPagePreviewType( type, processedExtract ) {
	if ( processedExtract === undefined ) {
		return previewTypes.TYPE_GENERIC;
	}

	switch ( type ) {
		case previewTypes.TYPE_GENERIC:
		case previewTypes.TYPE_DISAMBIGUATION:
		case previewTypes.TYPE_PAGE:
			return type;
		default:
			/**
			 * Assume type="page" if extract exists & not one of previewTypes.
			 * Note:
			 * - Restbase response includes "type" prop but other gateways don't.
			 * - event-logging Schema:Popups requires type="page" but restbase
			 * provides type="standard". Model must conform to event-logging schema.
			 */
			return previewTypes.TYPE_PAGE;
	}
}
