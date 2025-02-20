'use strict';

const { utils } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const { createRedirectForItem } = require( '../helpers/entityHelper' );
const {
	newRemoveItemLabelRequestBuilder,
	newSetItemLabelRequestBuilder,
	newCreateItemRequestBuilder
} = require( '../helpers/RequestBuilderFactory' );

function makeUnique( text ) {
	return `${text}-${utils.uniq()}`;
}

describe( newRemoveItemLabelRequestBuilder().getRouteDescription(), () => {

	let existingItemId;

	before( async () => {
		const createItemResponse = await newCreateItemRequestBuilder( {
			labels: { en: makeUnique( 'unique label' ) }
		} ).makeRequest();

		existingItemId = createItemResponse.body.id;
	} );

	describe( '200 OK', () => {
		after( async () => {
			// replace removed label
			await newSetItemLabelRequestBuilder( existingItemId, 'en', makeUnique( 'updated label' ) )
				.makeRequest();
		} );

		it( 'label removed', async () => {
			const response = await newRemoveItemLabelRequestBuilder( existingItemId, 'en' ).makeRequest();
			expect( response ).to.have.status( 200 );
			expect( response ).to.satisfyApiSchema;
		} );
	} );

	it( '400 - invalid language code', async () => {
		const response = await newRemoveItemLabelRequestBuilder( existingItemId, 'xyz' ).makeRequest();

		expect( response ).to.have.status( 400 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '404 - item does not exist', async () => {
		const response = await newRemoveItemLabelRequestBuilder( 'Q9999999', 'en' ).makeRequest();

		expect( response ).to.have.status( 404 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '409 - item redirected', async () => {
		const redirectSource = await createRedirectForItem( existingItemId );
		const response = await newRemoveItemLabelRequestBuilder( redirectSource, 'en' ).makeRequest();

		expect( response ).to.have.status( 409 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '412 - precondition failed', async () => {
		const yesterday = new Date( Date.now() - 24 * 60 * 60 * 1000 ).toUTCString();
		const response = await newRemoveItemLabelRequestBuilder( existingItemId, 'en' )
			.withHeader( 'If-Unmodified-Since', yesterday ).makeRequest();

		expect( response ).to.have.status( 412 );
		expect( response ).to.satisfyApiSchema;
	} );
} );
