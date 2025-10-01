'use strict';

const { assert } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const {
	getItemEditRequests,
	getPropertyEditRequests,
	getItemCreateRequest,
	getPropertyCreateRequest
} = require( '../helpers/happyPathRequestBuilders' );
const { describeWithTestData } = require( '../helpers/describeWithTestData' );

describeWithTestData(
	'Unsupported media type requests',
	( itemRequestInputs, propertyRequestInputs, describeEachRouteWithReset ) => {
		const requestsToTest = [
			...getItemEditRequests( itemRequestInputs ),
			...getPropertyEditRequests( propertyRequestInputs ),
			getItemCreateRequest( itemRequestInputs ),
			getPropertyCreateRequest( propertyRequestInputs )
		];
		describeEachRouteWithReset( requestsToTest, ( newRequestBuilder ) => {
			it( `${newRequestBuilder().getRouteDescription()} responds 415 for an unsupported media type`, async () => {
				const contentType = 'multipart/form-data';
				const response = await newRequestBuilder()
					// The following line is here to ensure a non-empty body for DELETE requests.
					.withJsonBodyParam( 'comment', '...' )
					.withHeader( 'content-type', contentType )
					.assertInvalidRequest()
					.makeRequest();

				expect( response ).to.have.status( 415 );
				assert.strictEqual( response.body.errorKey, 'rest-unsupported-content-type' );
			} );
		} );
	}
);
