'use strict';

/**
 * @return {string[]}
 */
const getSortMethods = function () {
	/**
	 * Known CirrusSearch sort methods as of 2020-09-14:
	 * - create_timestamp_asc
	 * - create_timestamp_desc
	 * - incoming_links_asc
	 * - incoming_links_desc
	 * - just_match
	 * - last_edit_asc
	 * - last_edit_desc
	 * - none
	 * - random
	 * - relevance
	 *
	 * @see \CirrusSearch\CirrusSearch::getValidSorts
	 * @see https://www.mediawiki.org/wiki/Help:CirrusSearch#Explicit_sort_orders
	 * @see https://codesearch.wmcloud.org/search/?q=public%20function%20getValidSorts
	 */
	// TODO change from static array to array derived from `getValidSorts` of the search engine on the PHP side,
	//      maybe with additional filter in the AdvancedSearch config (to provide a more streamlined UI).
	return [
		'relevance',
		'last_edit_desc',
		'create_timestamp_desc'
	];
};

module.exports = getSortMethods;
