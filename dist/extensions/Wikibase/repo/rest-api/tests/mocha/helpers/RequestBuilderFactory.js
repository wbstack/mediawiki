'use strict';

const { RequestBuilder } = require( './RequestBuilder' );
const { getAllowedBadges } = require( './getAllowedBadges' );

async function badgesConfig() {
	// eslint-disable-next-line es-x/no-object-fromentries
	return { badgeItems: Object.fromEntries( // TODO fix eslint config to allow this
		( await getAllowedBadges() ).map( ( badge ) => [ badge, '' ] )
	) };
}

module.exports = {
	newGetItemRequestBuilder( itemId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/items/{item_id}' )
			.withPathParam( 'item_id', itemId );
	},

	newCreateItemRequestBuilder( item ) {
		return new RequestBuilder()
			.withRoute( 'POST', '/entities/items' )
			.withJsonBodyParam( 'item', item );
	},

	newGetPropertyRequestBuilder( propertyId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/properties/{property_id}' )
			.withPathParam( 'property_id', propertyId );
	},

	newCreatePropertyRequestBuilder( property ) {
		return new RequestBuilder()
			.withRoute( 'POST', '/entities/properties' )
			.withJsonBodyParam( 'property', property );
	},

	newGetSitelinksRequestBuilder( itemId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/items/{item_id}/sitelinks' )
			.withPathParam( 'item_id', itemId );
	},

	newGetSitelinkRequestBuilder( itemId, siteId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/items/{item_id}/sitelinks/{site_id}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'site_id', siteId );
	},

	newGetItemAliasesRequestBuilder( itemId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/items/{item_id}/aliases' )
			.withPathParam( 'item_id', itemId );
	},

	newGetPropertyAliasesRequestBuilder( propertyId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/properties/{property_id}/aliases' )
			.withPathParam( 'property_id', propertyId );
	},

	newGetItemAliasesInLanguageRequestBuilder( itemId, languageCode ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/items/{item_id}/aliases/{language_code}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'language_code', languageCode );
	},

	newGetPropertyAliasesInLanguageRequestBuilder( propertyId, languageCode ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/properties/{property_id}/aliases/{language_code}' )
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'language_code', languageCode );
	},

	newGetItemDescriptionRequestBuilder( itemId, languageCode ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/items/{item_id}/descriptions/{language_code}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'language_code', languageCode );
	},

	newGetItemDescriptionWithFallbackRequestBuilder( itemId, languageCode ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/items/{item_id}/descriptions_with_language_fallback/{language_code}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'language_code', languageCode );
	},

	newGetPropertyDescriptionRequestBuilder( propertyId, languageCode ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/properties/{property_id}/descriptions/{language_code}' )
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'language_code', languageCode );
	},

	newGetPropertyDescriptionWithFallbackRequestBuilder( propertyId, languageCode ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/properties/{property_id}/descriptions_with_language_fallback/{language_code}' )
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'language_code', languageCode );
	},

	newGetItemDescriptionsRequestBuilder( itemId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/items/{item_id}/descriptions' )
			.withPathParam( 'item_id', itemId );
	},

	newGetPropertyDescriptionsRequestBuilder( propertyId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/properties/{property_id}/descriptions' )
			.withPathParam( 'property_id', propertyId );
	},

	newGetItemLabelsRequestBuilder( itemId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/items/{item_id}/labels' )
			.withPathParam( 'item_id', itemId );
	},

	newGetPropertyLabelsRequestBuilder( propertyId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/properties/{property_id}/labels' )
			.withPathParam( 'property_id', propertyId );
	},

	newPatchItemRequestBuilder( itemId, patch ) {
		return new RequestBuilder()
			.withRoute( 'PATCH', '/entities/items/{item_id}' )
			.withPathParam( 'item_id', itemId )
			.withJsonBodyParam( 'patch', patch )
			.withConfigOverride( 'wgWBRepoSettings', badgesConfig );
	},

	newPatchItemLabelsRequestBuilder( itemId, patch ) {
		return new RequestBuilder()
			.withRoute( 'PATCH', '/entities/items/{item_id}/labels' )
			.withPathParam( 'item_id', itemId )
			.withJsonBodyParam( 'patch', patch );
	},

	newPatchPropertyRequestBuilder( propertyId, patch ) {
		return new RequestBuilder()
			.withRoute( 'PATCH', '/entities/properties/{property_id}' )
			.withPathParam( 'property_id', propertyId )
			.withJsonBodyParam( 'patch', patch );
	},

	newPatchPropertyLabelsRequestBuilder( propertyId, patch ) {
		return new RequestBuilder()
			.withRoute( 'PATCH', '/entities/properties/{property_id}/labels' )
			.withPathParam( 'property_id', propertyId )
			.withJsonBodyParam( 'patch', patch );
	},

	newPatchPropertyDescriptionsRequestBuilder( propertyId, patch ) {
		return new RequestBuilder()
			.withRoute( 'PATCH', '/entities/properties/{property_id}/descriptions' )
			.withPathParam( 'property_id', propertyId )
			.withJsonBodyParam( 'patch', patch );
	},

	newPatchItemDescriptionsRequestBuilder( itemId, patch ) {
		return new RequestBuilder()
			.withRoute( 'PATCH', '/entities/items/{item_id}/descriptions' )
			.withPathParam( 'item_id', itemId )
			.withJsonBodyParam( 'patch', patch );
	},

	newPatchItemAliasesRequestBuilder( itemId, patch ) {
		return new RequestBuilder()
			.withRoute( 'PATCH', '/entities/items/{item_id}/aliases' )
			.withPathParam( 'item_id', itemId )
			.withJsonBodyParam( 'patch', patch );
	},

	newPatchPropertyAliasesRequestBuilder( propertyId, patch ) {
		return new RequestBuilder()
			.withRoute( 'PATCH', '/entities/properties/{property_id}/aliases' )
			.withPathParam( 'property_id', propertyId )
			.withJsonBodyParam( 'patch', patch );
	},

	newPatchSitelinksRequestBuilder( itemId, patch ) {
		return new RequestBuilder()
			.withRoute( 'PATCH', '/entities/items/{item_id}/sitelinks' )
			.withPathParam( 'item_id', itemId )
			.withJsonBodyParam( 'patch', patch )
			.withConfigOverride( 'wgWBRepoSettings', badgesConfig );
	},

	newGetItemLabelRequestBuilder( itemId, languageCode ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/items/{item_id}/labels/{language_code}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'language_code', languageCode );
	},

	newGetItemLabelWithFallbackRequestBuilder( itemId, languageCode ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/items/{item_id}/labels_with_language_fallback/{language_code}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'language_code', languageCode );
	},

	newGetPropertyLabelRequestBuilder( propertyId, languageCode ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/properties/{property_id}/labels/{language_code}' )
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'language_code', languageCode );
	},

	newGetPropertyLabelWithFallbackRequestBuilder( propertyId, languageCode ) {
		return new RequestBuilder()
			.withRoute(
				'GET',
				'/entities/properties/{property_id}/labels_with_language_fallback/{language_code}'
			)
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'language_code', languageCode );
	},

	newSetSitelinkRequestBuilder( itemId, siteId, sitelink ) {
		return new RequestBuilder()
			.withRoute( 'PUT', '/entities/items/{item_id}/sitelinks/{site_id}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'site_id', siteId )
			.withJsonBodyParam( 'sitelink', sitelink )
			.withConfigOverride( 'wgWBRepoSettings', badgesConfig );
	},

	newSetItemLabelRequestBuilder( itemId, languageCode, label ) {
		return new RequestBuilder()
			.withRoute( 'PUT', '/entities/items/{item_id}/labels/{language_code}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'language_code', languageCode )
			.withJsonBodyParam( 'label', label );
	},

	newSetPropertyLabelRequestBuilder( propertyId, languageCode, label ) {
		return new RequestBuilder()
			.withRoute( 'PUT', '/entities/properties/{property_id}/labels/{language_code}' )
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'language_code', languageCode )
			.withJsonBodyParam( 'label', label );
	},

	newSetItemDescriptionRequestBuilder( itemId, languageCode, description ) {
		return new RequestBuilder()
			.withRoute( 'PUT', '/entities/items/{item_id}/descriptions/{language_code}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'language_code', languageCode )
			.withJsonBodyParam( 'description', description );
	},

	newSetPropertyDescriptionRequestBuilder( propertyId, languageCode, description ) {
		return new RequestBuilder()
			.withRoute( 'PUT', '/entities/properties/{property_id}/descriptions/{language_code}' )
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'language_code', languageCode )
			.withJsonBodyParam( 'description', description );
	},

	newAddItemAliasesInLanguageRequestBuilder( itemId, languageCode, aliases ) {
		return new RequestBuilder()
			.withRoute( 'POST', '/entities/items/{item_id}/aliases/{language_code}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'language_code', languageCode )
			.withJsonBodyParam( 'aliases', aliases );
	},

	newAddPropertyAliasesInLanguageRequestBuilder( propertyId, languageCode, aliases ) {
		return new RequestBuilder()
			.withRoute( 'POST', '/entities/properties/{property_id}/aliases/{language_code}' )
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'language_code', languageCode )
			.withJsonBodyParam( 'aliases', aliases );
	},

	newRemoveSitelinkRequestBuilder( itemId, siteId ) {
		return new RequestBuilder()
			.withRoute( 'DELETE', '/entities/items/{item_id}/sitelinks/{site_id}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'site_id', siteId );
	},

	newRemovePropertyLabelRequestBuilder( propertyId, languageCode ) {
		return new RequestBuilder()
			.withRoute( 'DELETE', '/entities/properties/{property_id}/labels/{language_code}' )
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'language_code', languageCode );
	},

	newRemoveItemLabelRequestBuilder( itemId, languageCode ) {
		return new RequestBuilder()
			.withRoute( 'DELETE', '/entities/items/{item_id}/labels/{language_code}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'language_code', languageCode );
	},

	newRemoveItemDescriptionRequestBuilder( itemId, languageCode ) {
		return new RequestBuilder()
			.withRoute( 'DELETE', '/entities/items/{item_id}/descriptions/{language_code}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'language_code', languageCode );
	},

	newGetItemStatementsRequestBuilder( itemId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/items/{item_id}/statements' )
			.withPathParam( 'item_id', itemId );
	},

	newGetPropertyStatementsRequestBuilder( propertyId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/properties/{property_id}/statements' )
			.withPathParam( 'property_id', propertyId );
	},

	newAddItemStatementRequestBuilder( itemId, statement ) {
		return new RequestBuilder()
			.withRoute( 'POST', '/entities/items/{item_id}/statements' )
			.withPathParam( 'item_id', itemId )
			.withJsonBodyParam( 'statement', statement );
	},

	newAddPropertyStatementRequestBuilder( propertyId, statement ) {
		return new RequestBuilder()
			.withRoute( 'POST', '/entities/properties/{property_id}/statements' )
			.withPathParam( 'property_id', propertyId )
			.withJsonBodyParam( 'statement', statement );
	},

	newGetItemStatementRequestBuilder( itemId, statementId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/items/{item_id}/statements/{statement_id}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'statement_id', statementId );
	},

	newGetPropertyStatementRequestBuilder( propertyId, statementId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/entities/properties/{property_id}/statements/{statement_id}' )
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'statement_id', statementId );
	},

	newGetStatementRequestBuilder( statementId ) {
		return new RequestBuilder()
			.withRoute( 'GET', '/statements/{statement_id}' )
			.withPathParam( 'statement_id', statementId );
	},

	newReplaceItemStatementRequestBuilder( itemId, statementId, statement ) {
		return new RequestBuilder()
			.withRoute( 'PUT', '/entities/items/{item_id}/statements/{statement_id}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'statement_id', statementId )
			.withJsonBodyParam( 'statement', statement );
	},

	newReplacePropertyStatementRequestBuilder( propertyId, statementId, statement ) {
		return new RequestBuilder()
			.withRoute( 'PUT', '/entities/properties/{property_id}/statements/{statement_id}' )
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'statement_id', statementId )
			.withJsonBodyParam( 'statement', statement );
	},

	newReplaceStatementRequestBuilder( statementId, statement ) {
		return new RequestBuilder()
			.withRoute( 'PUT', '/statements/{statement_id}' )
			.withPathParam( 'statement_id', statementId )
			.withJsonBodyParam( 'statement', statement );
	},

	newRemoveItemStatementRequestBuilder( itemId, statementId ) {
		return new RequestBuilder()
			.withRoute( 'DELETE', '/entities/items/{item_id}/statements/{statement_id}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'statement_id', statementId );
	},

	newRemovePropertyStatementRequestBuilder( propertyId, statementId ) {
		return new RequestBuilder()
			.withRoute( 'DELETE', '/entities/properties/{property_id}/statements/{statement_id}' )
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'statement_id', statementId );
	},

	newRemoveStatementRequestBuilder( statementId ) {
		return new RequestBuilder()
			.withRoute( 'DELETE', '/statements/{statement_id}' )
			.withPathParam( 'statement_id', statementId );
	},

	newPatchItemStatementRequestBuilder( itemId, statementId, patch ) {
		return new RequestBuilder()
			.withRoute( 'PATCH', '/entities/items/{item_id}/statements/{statement_id}' )
			.withPathParam( 'item_id', itemId )
			.withPathParam( 'statement_id', statementId )
			.withJsonBodyParam( 'patch', patch );
	},

	newPatchPropertyStatementRequestBuilder( propertyId, statementId, patch ) {
		return new RequestBuilder()
			.withRoute( 'PATCH', '/entities/properties/{property_id}/statements/{statement_id}' )
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'statement_id', statementId )
			.withJsonBodyParam( 'patch', patch );
	},

	newPatchStatementRequestBuilder( statementId, patch ) {
		return new RequestBuilder()
			.withRoute( 'PATCH', '/statements/{statement_id}' )
			.withPathParam( 'statement_id', statementId )
			.withJsonBodyParam( 'patch', patch );
	},

	newRemovePropertyDescriptionRequestBuilder( propertyId, languageCode ) {
		return new RequestBuilder()
			.withRoute( 'DELETE', '/entities/properties/{property_id}/descriptions/{language_code}' )
			.withPathParam( 'property_id', propertyId )
			.withPathParam( 'language_code', languageCode );
	}

};
