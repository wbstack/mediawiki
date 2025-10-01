'use strict';

const { utils } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const {
	newPatchPropertyDescriptionsRequestBuilder,
	newCreatePropertyRequestBuilder
} = require( '../helpers/RequestBuilderFactory' );

describe( newPatchPropertyDescriptionsRequestBuilder().getRouteDescription(), () => {

	const langWithExistingDescription = 'en';
	let propertyId;

	function makeReplaceExistingDescriptionOp() {
		return {
			op: 'replace',
			path: `/${langWithExistingDescription}`,
			value: `test-description-${utils.uniq()}`
		};
	}

	before( async () => {
		const createPropertyResponse = await newCreatePropertyRequestBuilder( {
			data_type: 'string',
			descriptions: { [ langWithExistingDescription ]: `test-description-${utils.uniq()}`
			}
		} ).makeRequest();
		propertyId = createPropertyResponse.body.id;
	} );

	it( '200 OK', async () => {
		const response = await newPatchPropertyDescriptionsRequestBuilder(
			propertyId,
			[ makeReplaceExistingDescriptionOp() ]
		).makeRequest();

		expect( response ).to.have.status( 200 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '400 - invalid patch', async () => {
		const response = await newPatchPropertyDescriptionsRequestBuilder(
			propertyId,
			[ { invalid: 'patch' } ]
		).makeRequest();

		expect( response ).to.have.status( 400 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '404 - property not found', async () => {
		const response = await newPatchPropertyDescriptionsRequestBuilder(
			'P999999',
			[ makeReplaceExistingDescriptionOp() ]
		).makeRequest();

		expect( response ).to.have.status( 404 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '409 - patch test failed', async () => {
		const response = await newPatchPropertyDescriptionsRequestBuilder(
			propertyId,
			[ { op: 'test', path: '/en', value: 'unexpected description!' } ]
		).makeRequest();

		expect( response ).to.have.status( 409 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '412 - precondition failed', async () => {
		const yesterday = new Date( Date.now() - 24 * 60 * 60 * 1000 ).toUTCString();
		const response = await newPatchPropertyDescriptionsRequestBuilder(
			propertyId,
			[ makeReplaceExistingDescriptionOp() ]
		).withHeader( 'If-Unmodified-Since', yesterday ).makeRequest();

		expect( response ).to.have.status( 412 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '422 - empty description', async () => {
		const response = await newPatchPropertyDescriptionsRequestBuilder(
			propertyId,
			[ { op: 'replace', path: `/${langWithExistingDescription}`, value: '' } ]
		).makeRequest();

		expect( response ).to.have.status( 422 );
		expect( response ).to.satisfyApiSchema;
	} );

} );
