'use strict';

const { utils } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const { createRedirectForItem } = require( '../helpers/entityHelper' );
const {
	newSetItemLabelRequestBuilder,
	newCreateItemRequestBuilder
} = require( '../helpers/RequestBuilderFactory' );

function makeLabel( text ) {
	return `${text}-${utils.uniq()}`;
}

describe( newSetItemLabelRequestBuilder().getRouteDescription(), () => {

	let itemId;
	const langWithExistingLabel = 'en';

	before( async () => {
		const createItemResponse = await newCreateItemRequestBuilder( {
			labels: { [ langWithExistingLabel ]: makeLabel( 'en-label' ) }
		} ).makeRequest();

		itemId = createItemResponse.body.id;
	} );

	it( '200 - label replaced', async () => {
		const response = await newSetItemLabelRequestBuilder(
			itemId,
			langWithExistingLabel,
			makeLabel( 'updated label' )
		).makeRequest();
		expect( response ).to.have.status( 200 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '201 - label created', async () => {
		const response = await newSetItemLabelRequestBuilder(
			itemId,
			'de',
			makeLabel( 'neue Beschreibung' )
		).makeRequest();
		expect( response ).to.have.status( 201 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '400 - invalid (empty) label', async () => {
		const response = await newSetItemLabelRequestBuilder( itemId, 'de', '' )
			.makeRequest();
		expect( response ).to.have.status( 400 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '404 - item does not exist', async () => {
		const response = await newSetItemLabelRequestBuilder(
			'Q9999999',
			langWithExistingLabel,
			makeLabel( 'updated label' )
		).makeRequest();

		expect( response ).to.have.status( 404 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '409 - item redirected', async () => {
		const redirectSource = await createRedirectForItem( itemId );
		const response = await newSetItemLabelRequestBuilder(
			redirectSource,
			langWithExistingLabel,
			makeLabel( 'updated label' )
		).makeRequest();

		expect( response ).to.have.status( 409 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '412 - precondition failed', async () => {
		const yesterday = new Date( Date.now() - 24 * 60 * 60 * 1000 ).toUTCString();
		const response = await newSetItemLabelRequestBuilder(
			itemId,
			langWithExistingLabel,
			makeLabel( 'updated label' )
		)
			.withHeader( 'If-Unmodified-Since', yesterday )
			.makeRequest();

		expect( response ).to.have.status( 412 );
		expect( response ).to.satisfyApiSchema;
	} );
} );
