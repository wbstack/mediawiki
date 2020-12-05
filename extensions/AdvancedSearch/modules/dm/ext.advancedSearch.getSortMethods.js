( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.dm = mw.libs.advancedSearch.dm || {};

	mw.libs.advancedSearch.dm.getSortMethods = function () {
		// TODO change from static array to array derived from `getValidSorts` of the search engine on the PHP side,
		//      maybe with additional filter in the AdvancedSearch config (to provide a more streamlined UI).
		return [
			{
				name: 'relevance',
				label: mw.msg( 'advancedsearch-sort-relevance' ),
				previewLabel: mw.msg( 'advancedsearch-sort-preview-relevance' )
			},
			{
				name: 'last_edit_desc',
				label: mw.msg( 'advancedsearch-sort-last-edit-desc' ),
				previewLabel: mw.msg( 'advancedsearch-sort-preview-last-edit-desc' )
			},
			{
				name: 'create_timestamp_desc',
				label: mw.msg( 'advancedsearch-sort-create-timestamp-desc' ),
				previewLabel: mw.msg( 'advancedsearch-sort-preview-create-timestamp-desc' )
			}
		];
	};
}() );
