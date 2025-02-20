'use strict';

const { utils } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const {
	newRemovePropertyDescriptionRequestBuilder,
	newSetPropertyDescriptionRequestBuilder,
	newCreatePropertyRequestBuilder
} = require( '../helpers/RequestBuilderFactory' );

function makeUnique( text ) {
	return `${text}-${utils.uniq()}`;
}

describe( newRemovePropertyDescriptionRequestBuilder().getRouteDescription(), () => {

	let existingPropertyId;

	before( async () => {
		const createPropertyResponse = await newCreatePropertyRequestBuilder( {
			data_type: 'string',
			descriptions: { en: makeUnique( 'unique description' ) }
		} ).makeRequest();

		existingPropertyId = createPropertyResponse.body.id;
	} );

	describe( '200 OK', () => {
		after( async () => {
			// replace removed description
			await newSetPropertyDescriptionRequestBuilder(
				existingPropertyId,
				'en',
				makeUnique( 'updated description' )
			)
				.makeRequest();
		} );

		it( 'description removed', async () => {
			const response = await newRemovePropertyDescriptionRequestBuilder( existingPropertyId, 'en' ).makeRequest();
			expect( response ).to.have.status( 200 );
			expect( response ).to.satisfyApiSchema;
		} );
	} );

	it( '400 - invalid language code', async () => {
		const response = await newRemovePropertyDescriptionRequestBuilder( existingPropertyId, 'xyz' ).makeRequest();

		expect( response ).to.have.status( 400 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '404 - property does not exist', async () => {
		const response = await newRemovePropertyDescriptionRequestBuilder( 'P9999999', 'en' ).makeRequest();

		expect( response ).to.have.status( 404 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '412 - precondition failed', async () => {
		const yesterday = new Date( Date.now() - 24 * 60 * 60 * 1000 ).toUTCString();
		const response = await newRemovePropertyDescriptionRequestBuilder( existingPropertyId, 'en' )
			.withHeader( 'If-Unmodified-Since', yesterday ).makeRequest();

		expect( response ).to.have.status( 412 );
		expect( response ).to.satisfyApiSchema;
	} );
} );
