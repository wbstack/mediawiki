'use strict';

const { utils } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const { createRedirectForItem } = require( '../helpers/entityHelper' );
const {
	newSetItemDescriptionRequestBuilder,
	newCreateItemRequestBuilder
} = require( '../helpers/RequestBuilderFactory' );

function makeUnique( text ) {
	return `${text}-${utils.uniq()}`;
}

describe( newSetItemDescriptionRequestBuilder().getRouteDescription(), () => {

	let existingItemId;

	before( async () => {
		const createItemResponse = await newCreateItemRequestBuilder( {
			descriptions: { en: makeUnique( 'unique description' ) }
		} ).makeRequest();

		existingItemId = createItemResponse.body.id;
	} );

	it( '200 - description replaced', async () => {
		const response = await newSetItemDescriptionRequestBuilder(
			existingItemId,
			'en',
			makeUnique( 'updated description' )
		).makeRequest();
		expect( response ).to.have.status( 200 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '201 - description created', async () => {
		const response = await newSetItemDescriptionRequestBuilder(
			existingItemId,
			'de',
			makeUnique( 'neue Beschreibung' )
		).makeRequest();
		expect( response ).to.have.status( 201 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '400 - invalid description', async () => {
		const response = await newSetItemDescriptionRequestBuilder(
			existingItemId,
			'en',
			'tab character \t not allowed'
		).makeRequest();

		expect( response ).to.have.status( 400 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '404 - item does not exist', async () => {
		const response = await newSetItemDescriptionRequestBuilder(
			'Q9999999',
			'en',
			makeUnique( 'updated description' )
		).makeRequest();

		expect( response ).to.have.status( 404 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '409 - item redirected', async () => {
		const redirectSource = await createRedirectForItem( existingItemId );
		const response = await newSetItemDescriptionRequestBuilder(
			redirectSource,
			'en',
			makeUnique( 'updated description' )
		).makeRequest();

		expect( response ).to.have.status( 409 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '412 - precondition failed', async () => {
		const yesterday = new Date( Date.now() - 24 * 60 * 60 * 1000 ).toUTCString();
		const response = await newSetItemDescriptionRequestBuilder(
			existingItemId,
			'en',
			makeUnique( 'updated description' )
		)
			.withHeader( 'If-Unmodified-Since', yesterday )
			.makeRequest();

		expect( response ).to.have.status( 412 );
		expect( response ).to.satisfyApiSchema;
	} );
} );
