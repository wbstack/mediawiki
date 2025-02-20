'use strict';

const { utils } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const {
	newPatchItemDescriptionsRequestBuilder,
	newCreateItemRequestBuilder
} = require( '../helpers/RequestBuilderFactory' );

describe( newPatchItemDescriptionsRequestBuilder().getRouteDescription(), () => {

	const langWithExistingDescription = 'en';
	let itemId;

	function makeReplaceExistingDescriptionOp() {
		return {
			op: 'replace',
			path: `/${langWithExistingDescription}`,
			value: `test-description-${utils.uniq()}`
		};
	}

	before( async () => {
		const createItemResponse = await newCreateItemRequestBuilder( {
			descriptions: { [ langWithExistingDescription ]: `test-description-${utils.uniq()}` }
		} ).makeRequest();
		itemId = createItemResponse.body.id;
	} );

	it( '200 OK', async () => {
		const response = await newPatchItemDescriptionsRequestBuilder(
			itemId,
			[ makeReplaceExistingDescriptionOp() ]
		).makeRequest();

		expect( response ).to.have.status( 200 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '400 - invalid patch', async () => {
		const response = await newPatchItemDescriptionsRequestBuilder(
			itemId,
			[ { invalid: 'patch' } ]
		).makeRequest();

		expect( response ).to.have.status( 400 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '404 - item not found', async () => {
		const response = await newPatchItemDescriptionsRequestBuilder(
			'Q999999',
			[ makeReplaceExistingDescriptionOp() ]
		).makeRequest();

		expect( response ).to.have.status( 404 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '409 - patch test failed', async () => {
		const response = await newPatchItemDescriptionsRequestBuilder(
			itemId,
			[ { op: 'test', path: '/en', value: 'unexpected description!' } ]
		).makeRequest();

		expect( response ).to.have.status( 409 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '412 - precondition failed', async () => {
		const yesterday = new Date( Date.now() - 24 * 60 * 60 * 1000 ).toUTCString();
		const response = await newPatchItemDescriptionsRequestBuilder(
			itemId,
			[ makeReplaceExistingDescriptionOp() ]
		).withHeader( 'If-Unmodified-Since', yesterday ).makeRequest();

		expect( response ).to.have.status( 412 );
		expect( response ).to.satisfyApiSchema;
	} );

	it( '422 - empty description', async () => {
		const response = await newPatchItemDescriptionsRequestBuilder(
			itemId,
			[ { op: 'replace', path: `/${langWithExistingDescription}`, value: '' } ]
		).makeRequest();

		expect( response ).to.have.status( 422 );
		expect( response ).to.satisfyApiSchema;
	} );

} );
