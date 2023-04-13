'use strict';

const { assert, action } = require( 'api-testing' );
const entityHelper = require( '../helpers/entityHelper' );
const hasJsonDiffLib = require( '../helpers/hasJsonDiffLib' );
const { RequestBuilder } = require( '../helpers/RequestBuilder' );

function makeEtag( ...revisionIds ) {
	return revisionIds.map( ( revId ) => `"${revId}"` ).join( ',' );
}

function assertValid400Response( response, responseBodyErrorCode ) {
	assert.strictEqual( response.status, 400 );
	assert.header( response, 'Content-Language', 'en' );
	assert.strictEqual( response.body.code, responseBodyErrorCode );
}

let testItemId;
let testPropertyId;
let testStatementId;
let originalLastModified;
let originalRevisionId;

describe( 'PATCH statement tests ', () => {

	before( async function () {
		if ( !hasJsonDiffLib() ) {
			this.skip(); // awaiting security review (T316245)
		}

		testPropertyId = ( await entityHelper.createUniqueStringProperty() ).entity.id;

		const createItemResponse = await entityHelper.createEntity( 'item', {
			claims: [ entityHelper.newStatementWithRandomStringValue( testPropertyId ) ]
		} );
		testItemId = createItemResponse.entity.id;
		testStatementId = createItemResponse.entity.claims[ testPropertyId ][ 0 ].id;

		const testItemCreationMetadata = await entityHelper.getLatestEditMetadata( testItemId );
		originalLastModified = new Date( testItemCreationMetadata.timestamp );
		originalRevisionId = testItemCreationMetadata.revid;

		// wait 1s before adding any statements to verify the last-modified timestamps are different
		await new Promise( ( resolve ) => {
			setTimeout( resolve, 1000 );
		} );
	} );

	[ // eslint-disable-line mocha/no-setup-in-describe
		{
			route: 'PATCH /entities/items/{item_id}/statements/{statement_id}',
			newPatchRequestBuilder: ( statementId, patch ) => new RequestBuilder()
				.withRoute( 'PATCH', '/entities/items/{item_id}/statements/{statement_id}' )
				.withPathParam( 'item_id', testItemId )
				.withPathParam( 'statement_id', statementId )
				.withJsonBodyParam( 'patch', patch )
		},
		{
			route: 'PATCH /statements/{statement_id}',
			newPatchRequestBuilder: ( statementId, patch ) => new RequestBuilder()
				.withRoute( 'PATCH', '/statements/{statement_id}' )
				.withPathParam( 'statement_id', statementId )
				.withJsonBodyParam( 'patch', patch )
		}
	].forEach( ( { route, newPatchRequestBuilder } ) => {
		describe( route, () => {

			function assertValid200Response( response ) {
				assert.strictEqual( response.status, 200 );
				assert.strictEqual( response.body.id, testStatementId );
				assert.strictEqual( response.header[ 'content-type' ], 'application/json' );
				assert.isAbove( new Date( response.header[ 'last-modified' ] ), originalLastModified );
				assert.notStrictEqual( response.header.etag, makeEtag( originalRevisionId ) );
			}

			describe( '200 success response', () => {

				afterEach( async () => {
					await new RequestBuilder() // reset after successful edit
						.withRoute( 'PUT', '/statements/{statement_id}' )
						.withPathParam( 'statement_id', testStatementId )
						.withJsonBodyParam(
							'statement',
							entityHelper.newStatementWithRandomStringValue( testPropertyId )
						)
						.makeRequest();
				} );

				it( 'can patch a statement', async () => {
					const expectedValue = 'i been patched!!';
					const response = await newPatchRequestBuilder( testStatementId, [
						{
							op: 'replace',
							path: '/mainsnak/datavalue/value',
							value: expectedValue
						}
					] ).assertValidRequest().makeRequest();

					assertValid200Response( response );
					assert.strictEqual( response.body.mainsnak.datavalue.value, expectedValue );
				} );

				it( 'allows content-type application/json-patch+json', async () => {
					const expectedValue = 'i been patched again!!';
					const response = await newPatchRequestBuilder( testStatementId, [
						{
							op: 'replace',
							path: '/mainsnak/datavalue/value',
							value: expectedValue
						}
					] )
						.withHeader( 'content-type', 'application/json-patch+json' )
						.assertValidRequest().makeRequest();

					assertValid200Response( response );
					assert.strictEqual( response.body.mainsnak.datavalue.value, expectedValue );
				} );

				it( 'can patch a statement with edit metadata', async () => {
					const user = await action.mindy();
					const tag = await action.makeTag( 'e2e test tag', 'Created during e2e test' );
					const editSummary = 'i made a patch';
					const expectedValue = `${user.username} was here`;
					const response = await newPatchRequestBuilder( testStatementId, [
						{
							op: 'replace',
							path: '/mainsnak/datavalue/value',
							value: expectedValue
						}
					] ).withJsonBodyParam( 'tags', [ tag ] )
						.withJsonBodyParam( 'bot', true )
						.withJsonBodyParam( 'comment', editSummary )
						.withUser( user )
						.assertValidRequest().makeRequest();

					assertValid200Response( response );
					assert.strictEqual( response.body.mainsnak.datavalue.value, expectedValue );

					const editMetadata = await entityHelper.getLatestEditMetadata( testItemId );
					assert.include( editMetadata.tags, tag );
					assert.property( editMetadata, 'bot' );
					assert.strictEqual( editMetadata.comment, editSummary );
					assert.strictEqual( editMetadata.user, user.username );
				} );

			} );

			describe( '400 error response', () => {

				it( 'statement ID contains invalid entity ID', async () => {
					const statementId = testStatementId.replace( 'Q', 'X' );
					const response = await newPatchRequestBuilder( statementId, [] )
						.assertInvalidRequest().makeRequest();

					assertValid400Response( response, 'invalid-statement-id' );
					assert.include( response.body.message, statementId );
				} );

				it( 'statement ID is invalid format', async () => {
					const statementId = 'not-a-valid-format';
					const response = await newPatchRequestBuilder( statementId, [] )
						.assertInvalidRequest().makeRequest();

					assertValid400Response( response, 'invalid-statement-id' );
					assert.include( response.body.message, statementId );
				} );

				it( 'statement is not on an item', async () => {
					const statementId = testStatementId.replace( 'Q', 'P' );
					const response = await newPatchRequestBuilder( statementId, [] )
						.assertValidRequest().makeRequest();

					assertValid400Response( response, 'invalid-statement-id' );
					assert.include( response.body.message, statementId );
				} );

				it( 'comment too long', async () => {
					const comment = 'x'.repeat( 501 );
					const response = await newPatchRequestBuilder( testStatementId, [] )
						.withJsonBodyParam( 'comment', comment ).assertValidRequest().makeRequest();

					assertValid400Response( response, 'comment-too-long' );
					assert.include( response.body.message, '500' );
				} );

				it( 'invalid patch', async () => {
					const invalidPatch = { patch: 'this is not a valid JSON Patch' };
					const response = await newPatchRequestBuilder( testStatementId, invalidPatch )
						.assertInvalidRequest().makeRequest();

					assertValid400Response( response, 'invalid-patch' );
				} );

				it( 'invalid edit tag', async () => {
					const invalidEditTag = 'invalid tag';
					const response = await newPatchRequestBuilder( testStatementId, [] )
						.withJsonBodyParam( 'tags', [ invalidEditTag ] ).assertValidRequest().makeRequest();

					assertValid400Response( response, 'invalid-edit-tag' );
					assert.include( response.body.message, invalidEditTag );
				} );

				it( 'invalid edit tag type', async () => {
					const response = await newPatchRequestBuilder( testStatementId, [] )
						.withJsonBodyParam( 'tags', 'not an array' ).assertInvalidRequest().makeRequest();

					assert.strictEqual( response.status, 400 );
					assert.strictEqual( response.body.code, 'invalid-request-body' );
					assert.strictEqual( response.body.fieldName, 'tags' );
					assert.strictEqual( response.body.expectedType, 'array' );
				} );

				it( 'invalid bot flag type', async () => {
					const response = await newPatchRequestBuilder( testStatementId, [] )
						.withJsonBodyParam( 'bot', 'not boolean' ).assertInvalidRequest().makeRequest();

					assert.strictEqual( response.status, 400 );
					assert.strictEqual( response.body.code, 'invalid-request-body' );
					assert.strictEqual( response.body.fieldName, 'bot' );
					assert.strictEqual( response.body.expectedType, 'boolean' );
				} );

				it( 'invalid comment type', async () => {
					const response = await newPatchRequestBuilder( testStatementId, [] )
						.withJsonBodyParam( 'comment', 1234 ).assertInvalidRequest().makeRequest();

					assert.strictEqual( response.status, 400 );
					assert.strictEqual( response.body.code, 'invalid-request-body' );
					assert.strictEqual( response.body.fieldName, 'comment' );
					assert.strictEqual( response.body.expectedType, 'string' );
				} );

			} );

		} );

	} );

	describe( 'item specific 400 error response', () => {
		function newPatchItemStatementRequestBuilder( itemId, statementId, patch ) {
			return new RequestBuilder()
				.withRoute( 'PATCH', '/entities/items/{item_id}/statements/{statement_id}' )
				.withPathParam( 'item_id', itemId )
				.withPathParam( 'statement_id', statementId )
				.withJsonBodyParam( 'patch', patch );
		}

		it( 'invalid item ID', async () => {
			const itemId = 'X123';
			const response = await newPatchItemStatementRequestBuilder( itemId, testStatementId, { } )
				.assertInvalidRequest()
				.makeRequest();

			assertValid400Response( response, 'invalid-item-id' );
			assert.include( response.body.message, itemId );
		} );

	} );

} );
