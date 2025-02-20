'use strict';

const { assert } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const { getLatestEditMetadata, createRedirectForItem } = require( '../helpers/entityHelper' );
const {
	newGetItemDescriptionsRequestBuilder,
	newCreateItemRequestBuilder
} = require( '../helpers/RequestBuilderFactory' );
const { assertValidError } = require( '../helpers/responseValidator' );

describe( newGetItemDescriptionsRequestBuilder().getRouteDescription(), () => {
	let itemId;

	before( async () => {
		const createItemResponse = await newCreateItemRequestBuilder(
			{ descriptions: { en: 'English science fiction writer and humorist' } }
		).makeRequest();
		itemId = createItemResponse.body.id;
	} );

	it( 'can get the descriptions of an item', async () => {
		const testItemCreationMetadata = await getLatestEditMetadata( itemId );

		const response = await newGetItemDescriptionsRequestBuilder( itemId )
			.assertValidRequest()
			.makeRequest();

		expect( response ).to.have.status( 200 );
		assert.deepEqual( response.body, { en: 'English science fiction writer and humorist' } );
		assert.strictEqual( response.header.etag, `"${testItemCreationMetadata.revid}"` );
		assert.strictEqual( response.header[ 'last-modified' ], testItemCreationMetadata.timestamp );
	} );

	it( '400 error - bad request, invalid item ID', async () => {
		const invalidItemId = 'X123';
		const response = await newGetItemDescriptionsRequestBuilder( invalidItemId )
			.assertInvalidRequest().makeRequest();

		assertValidError(
			response,
			400,
			'invalid-path-parameter',
			{ parameter: 'item_id' }
		);
	} );

	it( 'responds 404 in case the item does not exist', async () => {
		const nonExistentItemId = 'Q99999999';
		const response = await newGetItemDescriptionsRequestBuilder( nonExistentItemId )
			.assertValidRequest().makeRequest();

		assertValidError( response, 404, 'resource-not-found', { resource_type: 'item' } );
		assert.strictEqual( response.body.message, 'The requested resource does not exist' );
	} );

	it( '308 - item redirected', async () => {
		const redirectTarget = itemId;
		const redirectSource = await createRedirectForItem( redirectTarget );

		const response = await newGetItemDescriptionsRequestBuilder( redirectSource )
			.assertValidRequest()
			.makeRequest();

		expect( response ).to.have.status( 308 );
		assert.isTrue(
			new URL( response.headers.location ).pathname
				.endsWith( `rest.php/wikibase/v1/entities/items/${redirectTarget}/descriptions` )
		);
	} );
} );
