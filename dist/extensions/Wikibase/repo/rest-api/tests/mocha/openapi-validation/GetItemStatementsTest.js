'use strict';

const chai = require( 'chai' );
const { createEntity, createSingleItem, createRedirectForItem } = require( '../helpers/entityHelper' );
const { RequestBuilder } = require( '../helpers/RequestBuilder' );
const expect = chai.expect;

function newGetItemStatementsRequestBuilder( itemId ) {
	return new RequestBuilder()
		.withRoute( 'GET', '/entities/items/{item_id}/statements' )
		.withPathParam( 'item_id', itemId );
}

describe( 'validate GET /entities/items/{id}/statements responses against OpenAPI spec', () => {

	let itemId;
	let latestRevisionId;

	before( async () => {
		const createItemResponse = await createEntity( 'item', {} );
		itemId = createItemResponse.entity.id;
		latestRevisionId = createItemResponse.entity.lastrevid;
	} );

	it( '200 OK response is valid for an Item with no statements', async () => {
		const response = await newGetItemStatementsRequestBuilder( itemId ).makeRequest();

		expect( response.status ).to.equal( 200 );
		expect( response ).to.satisfyApiSpec;
	} );

	it( '200 OK response is valid for an Item with statements', async () => {
		const { entity: { id } } = await createSingleItem();
		const response = await newGetItemStatementsRequestBuilder( id ).makeRequest();

		expect( response.status ).to.equal( 200 );
		expect( response ).to.satisfyApiSpec;
	} );

	it( '304 Not Modified response is valid', async () => {
		const response = await newGetItemStatementsRequestBuilder( itemId )
			.withHeader( 'If-None-Match', `"${latestRevisionId}"` )
			.makeRequest();

		expect( response.status ).to.equal( 304 );
		expect( response ).to.satisfyApiSpec;
	} );

	it( '308 Permanent Redirect response is valid for a redirected item', async () => {
		const redirectSourceId = await createRedirectForItem( itemId );

		const response = await newGetItemStatementsRequestBuilder( redirectSourceId ).makeRequest();

		expect( response.status ).to.equal( 308 );
		expect( response ).to.satisfyApiSpec;
	} );

	it( '400 Bad Request response is valid for an invalid item ID', async () => {
		const response = await newGetItemStatementsRequestBuilder( 'X123' ).makeRequest();

		expect( response.status ).to.equal( 400 );
		expect( response ).to.satisfyApiSpec;
	} );

	it( '404 Not Found response is valid for a non-existing item', async () => {
		const response = await await newGetItemStatementsRequestBuilder( 'Q99999' ).makeRequest();

		expect( response.status ).to.equal( 404 );
		expect( response ).to.satisfyApiSpec;
	} );

} );
