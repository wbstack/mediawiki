'use strict';

const { assert, utils } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const { getLatestEditMetadata } = require( '../helpers/entityHelper' );
const {
	newGetPropertyLabelRequestBuilder,
	newCreatePropertyRequestBuilder
} = require( '../helpers/RequestBuilderFactory' );
const { assertValidError } = require( '../helpers/responseValidator' );

describe( newGetPropertyLabelRequestBuilder().getRouteDescription(), () => {
	let propertyId;
	const propertyEnLabel = `en-label-${utils.uniq()}`;

	before( async () => {
		const testProperty = await newCreatePropertyRequestBuilder(
			{ data_type: 'string', labels: { en: propertyEnLabel } }
		).makeRequest();
		propertyId = testProperty.body.id;
	} );

	it( 'can get a label of a property', async () => {
		const response = await newGetPropertyLabelRequestBuilder( propertyId, 'en' ).assertValidRequest().makeRequest();

		expect( response ).to.have.status( 200 );
		assert.strictEqual( response.body, propertyEnLabel );

		const testPropertyCreationMetadata = await getLatestEditMetadata( propertyId );
		assert.strictEqual( response.header.etag, `"${testPropertyCreationMetadata.revid}"` );
		assert.strictEqual( response.header[ 'last-modified' ], testPropertyCreationMetadata.timestamp );
	} );

	it( 'responds 404 if the property does not exist', async () => {
		const nonExistentProperty = 'P99999999';
		const response = await newGetPropertyLabelRequestBuilder( nonExistentProperty, 'en' )
			.assertValidRequest()
			.makeRequest();

		assertValidError( response, 404, 'resource-not-found', { resource_type: 'property' } );
		assert.strictEqual( response.body.message, 'The requested resource does not exist' );
	} );

	it( 'responds 404 if the label does not exist', async () => {
		const languageCodeWithNoDefinedLabel = 'ko';
		const response = await newGetPropertyLabelRequestBuilder( propertyId, languageCodeWithNoDefinedLabel )
			.assertValidRequest()
			.makeRequest();

		assertValidError( response, 404, 'resource-not-found', { resource_type: 'label' } );
		assert.strictEqual( response.body.message, 'The requested resource does not exist' );
	} );

	it( '400 - invalid property ID', async () => {
		const response = await newGetPropertyLabelRequestBuilder( 'X123', 'en' )
			.assertInvalidRequest()
			.makeRequest();

		assertValidError(
			response,
			400,
			'invalid-path-parameter',
			{ parameter: 'property_id' }
		);
	} );

	it( '400 - invalid language code', async () => {
		const response = await newGetPropertyLabelRequestBuilder( propertyId, '1e' )
			.assertInvalidRequest()
			.makeRequest();

		assertValidError(
			response,
			400,
			'invalid-path-parameter',
			{ parameter: 'language_code' }
		);
	} );

} );
