'use strict';

const { utils } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const { createRedirectForItem, getLatestEditMetadata } = require( '../helpers/entityHelper' );
const { newGetItemLabelsRequestBuilder, newCreateItemRequestBuilder } = require( '../helpers/RequestBuilderFactory' );

describe( newGetItemLabelsRequestBuilder().getRouteDescription(), () => {

	let testItemId;
	let lastRevisionId;

	before( async () => {
		const createItemResponse = await newCreateItemRequestBuilder( {
			labels: {
				de: 'a-German-label-' + utils.uniq(),
				en: 'an-English-label-' + utils.uniq()
			}
		} ).makeRequest();
		testItemId = createItemResponse.body.id;
		lastRevisionId = ( await getLatestEditMetadata( testItemId ) ).revid;
	} );

	it( '200 OK response is valid for an Item with two labels', async () => {
		const response = await newGetItemLabelsRequestBuilder( testItemId ).makeRequest();

		expect( response ).to.have.status( 200 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '304 Not Modified response is valid', async () => {
		const response = await newGetItemLabelsRequestBuilder( testItemId )
			.withHeader( 'If-None-Match', `"${lastRevisionId}"` )
			.makeRequest();

		expect( response ).to.have.status( 304 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '308 Permanent Redirect response is valid for a redirected item', async () => {
		const redirectSourceId = await createRedirectForItem( testItemId );

		const response = await newGetItemLabelsRequestBuilder( redirectSourceId ).makeRequest();

		expect( response ).to.have.status( 308 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '400 Bad Request response is valid for an invalid item ID', async () => {
		const response = await newGetItemLabelsRequestBuilder( 'X123' ).makeRequest();

		expect( response ).to.have.status( 400 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '404 Not Found response is valid for a non-existing item', async () => {
		const response = await newGetItemLabelsRequestBuilder( 'Q99999' ).makeRequest();

		expect( response ).to.have.status( 404 );
		expect( response ).to.satisfyApiSchema;
	} );

} );
