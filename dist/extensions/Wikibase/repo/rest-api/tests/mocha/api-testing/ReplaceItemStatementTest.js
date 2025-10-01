'use strict';

const { assert, action } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const entityHelper = require( '../helpers/entityHelper' );
const { formatStatementEditSummary } = require( '../helpers/formatEditSummaries' );
const {
	newReplaceItemStatementRequestBuilder,
	newReplaceStatementRequestBuilder,
	newGetItemStatementsRequestBuilder,
	newCreatePropertyRequestBuilder,
	newCreateItemRequestBuilder
} = require( '../helpers/RequestBuilderFactory' );
const { makeEtag } = require( '../helpers/httpHelper' );
const { assertValidError } = require( '../helpers/responseValidator' );
const { getOrCreateBotUser } = require( '../helpers/testUsers' );

describe( 'PUT statement tests', () => {
	let testItemId;
	let testStatementId;
	let predicatePropertyId;
	let originalLastModified;
	let originalRevisionId;

	before( async () => {
		predicatePropertyId = ( await entityHelper.createUniqueStringProperty() ).body.id;
		const createItemResponse = await entityHelper.createItemWithStatements( [
			entityHelper.newStatementWithRandomStringValue( predicatePropertyId )
		] );
		testItemId = createItemResponse.id;
		testStatementId = createItemResponse.statements[ predicatePropertyId ][ 0 ].id;

		const testItemCreationMetadata = await entityHelper.getLatestEditMetadata( testItemId );
		originalLastModified = new Date( testItemCreationMetadata.timestamp );
		originalRevisionId = testItemCreationMetadata.revid;

		// wait 1s before next test to ensure the last-modified timestamps are different
		await new Promise( ( resolve ) => {
			setTimeout( resolve, 1000 );
		} );
	} );

	[
		newReplaceItemStatementRequestBuilder,
		( itemId, statementId, statement ) => newReplaceStatementRequestBuilder( statementId, statement )
	].forEach( ( newReplaceRequestBuilder ) => {
		describe( newReplaceRequestBuilder().getRouteDescription(), () => {

			function assertValid200Response( response ) {
				expect( response ).to.have.status( 200 );
				assert.strictEqual( response.body.id, testStatementId );
				assert.strictEqual( response.header[ 'content-type' ], 'application/json' );
				assert.isAbove( new Date( response.header[ 'last-modified' ] ), originalLastModified );
				assert.notStrictEqual( response.header.etag, makeEtag( originalRevisionId ) );
			}

			describe( '200 success response ', () => {

				it( 'can replace a statement to an item with edit metadata omitted', async () => {
					const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
					const response = await newReplaceRequestBuilder( testItemId, testStatementId, statement )
						.assertValidRequest().makeRequest();

					assertValid200Response( response );

					assert.deepEqual( response.body.value.content, statement.value.content );
					const { comment } = await entityHelper.getLatestEditMetadata( testItemId );
					assert.strictEqual(
						comment,
						formatStatementEditSummary(
							'wbsetclaim',
							'update',
							statement.property.id,
							statement.value.content
						)
					);
				} );

				it( 'can replace a statement to an item with edit metadata provided', async () => {
					const user = await getOrCreateBotUser();
					const tag = await action.makeTag( 'e2e test tag', 'Created during e2e test', true );
					const editSummary = 'omg look i made an edit';
					const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
					const response = await newReplaceRequestBuilder( testItemId, testStatementId, statement )
						.withJsonBodyParam( 'tags', [ tag ] )
						.withJsonBodyParam( 'bot', true )
						.withJsonBodyParam( 'comment', editSummary )
						.withUser( user )
						.assertValidRequest().makeRequest();

					assertValid200Response( response );
					assert.deepEqual( response.body.value.content, statement.value.content );

					const editMetadata = await entityHelper.getLatestEditMetadata( testItemId );
					assert.deepEqual( editMetadata.tags, [ tag ] );
					assert.property( editMetadata, 'bot' );
					assert.strictEqual(
						editMetadata.comment,
						formatStatementEditSummary(
							'wbsetclaim',
							'update',
							statement.property.id,
							statement.value.content,
							editSummary
						)
					);
					assert.strictEqual( editMetadata.user, user.username );
				} );

				it( 'is idempotent: repeating the same request only results in one edit', async () => {
					const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
					const requestTemplate = newReplaceRequestBuilder( testItemId, testStatementId, statement )
						.assertValidRequest();

					const response1 = await requestTemplate.makeRequest();
					const response2 = await requestTemplate.makeRequest();

					assertValid200Response( response1 );
					assertValid200Response( response2 );

					assert.strictEqual( response1.headers.etag, response2.headers.etag );
					assert.strictEqual( response1.headers[ 'last-modified' ], response2.headers[ 'last-modified' ] );
					assert.deepEqual( response1.body, response2.body );
				} );

				it( 'replaces the statement in place without changing the order', async () => {
					// This is tested here by creating a new test item with three statements, replacing the
					// middle one and then checking that it's still in the middle afterwards.
					const newTestItem = ( await entityHelper.createItemWithStatements( [
						entityHelper.newStatementWithRandomStringValue( predicatePropertyId ),
						entityHelper.newStatementWithRandomStringValue( predicatePropertyId ),
						entityHelper.newStatementWithRandomStringValue( predicatePropertyId )
					] ) );

					const originalSecondStatement = newTestItem.statements[ predicatePropertyId ][ 1 ];
					const newSecondStatement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );

					await newReplaceRequestBuilder( newTestItem.id, originalSecondStatement.id, newSecondStatement )
						.makeRequest();

					const actualSecondStatement = (
						await newGetItemStatementsRequestBuilder( newTestItem.id ).makeRequest()
					).body[ predicatePropertyId ][ 1 ];

					assert.strictEqual( actualSecondStatement.id, originalSecondStatement.id );
					assert.strictEqual( actualSecondStatement.value.content, newSecondStatement.value.content );
					assert.notEqual(
						actualSecondStatement.value.content,
						originalSecondStatement.value.content
					);
				} );

			} );

			describe( '400 error response', () => {
				it( 'statement ID contains invalid entity ID', async () => {
					const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
					const response = await newReplaceRequestBuilder( testItemId, testStatementId.replace( 'Q', 'X' ), statement )
						.assertInvalidRequest().makeRequest();

					assertValidError(
						response,
						400,
						'invalid-path-parameter',
						{ parameter: 'statement_id' }
					);
				} );

				it( 'statement ID is invalid format', async () => {
					const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
					const response = await newReplaceRequestBuilder( testItemId, 'not-a-valid-format', statement )
						.assertInvalidRequest().makeRequest();

					assertValidError(
						response,
						400,
						'invalid-path-parameter',
						{ parameter: 'statement_id' }
					);
				} );

				it( 'invalid operation - new statement has a different Statement ID', async () => {
					const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
					statement.id = testItemId + '$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
					const response = await newReplaceRequestBuilder( testItemId, testStatementId, statement )
						.assertInvalidRequest().makeRequest();

					assertValidError(
						response,
						400,
						'cannot-modify-read-only-value',
						{ path: '/statement/id' }
					);
				} );

				it( 'invalid operation - new statement has a different Property ID', async () => {
					const differentPropertyId = ( await newCreatePropertyRequestBuilder(
						{ data_type: 'string' }
					).makeRequest() ).body.id;
					const statement = entityHelper.newStatementWithRandomStringValue( differentPropertyId );
					const response = await newReplaceRequestBuilder( testItemId, testStatementId, statement )
						.assertValidRequest().makeRequest();

					assertValidError(
						response,
						400,
						'cannot-modify-read-only-value',
						{ path: '/statement/property/id' }
					);
				} );

				it( 'invalid statement type: string', async () => {
					const response = await newReplaceRequestBuilder( testItemId, testStatementId, 'statement-not-string' )
						.assertInvalidRequest().makeRequest();

					expect( response ).to.have.status( 400 );
					assert.strictEqual( response.body.code, 'invalid-value' );
					assert.deepEqual( response.body.context, { path: '/statement' } );
				} );

				it( 'invalid statement type: array', async () => {
					const response = await newReplaceRequestBuilder( testItemId, testStatementId, [ 'statement-not-array' ] )
						.assertInvalidRequest().makeRequest();

					assertValidError( response, 400, 'invalid-value', { path: '/statement' } );
				} );

				it( 'invalid statement field', async () => {
					const invalidStatement = { property: { id: [ 'P1' ] }, value: { type: 'novalue' } };

					const response = await newReplaceRequestBuilder( testItemId, testStatementId, invalidStatement )
						.assertInvalidRequest().makeRequest();

					assertValidError( response, 400, 'invalid-value', { path: '/statement/property/id' } );
					assert.include( response.body.message, 'statement/property/id' );
				} );

				it( 'missing top-level field', async () => {
					const response = await newReplaceRequestBuilder( testItemId, testStatementId, {} )
						.withEmptyJsonBody()
						.assertInvalidRequest()
						.makeRequest();

					expect( response ).to.have.status( 400 );
					assert.strictEqual( response.body.code, 'missing-field' );
					assert.deepEqual( response.body.context, { path: '', field: 'statement' } );
					assert.strictEqual( response.body.message, 'Required field missing' );
				} );

				it( 'missing statement field', async () => {
					const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
					delete statement.property.id;

					const response = await newReplaceRequestBuilder( testItemId, testStatementId, statement )
						.assertInvalidRequest().makeRequest();

					assertValidError( response, 400, 'missing-field', { path: '/statement/property', field: 'id' } );
					assert.strictEqual( response.body.message, 'Required field missing' );
				} );

				it( 'qualifier with non-existent property', async () => {
					const nonExistentProperty = 'P9999999';
					const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
					statement.qualifiers = [
						{ property: { id: nonExistentProperty }, value: { type: 'novalue' } }
					];

					const response = await newReplaceRequestBuilder( testItemId, testStatementId, statement )
						.assertValidRequest().makeRequest();

					assertValidError(
						response,
						400,
						'referenced-resource-not-found',
						{ path: '/statement/qualifiers/0/property/id' }
					);
					assert.strictEqual( response.body.message, 'The referenced resource does not exist' );
				} );

				it( 'reference with non-existent property', async () => {
					const nonExistentProperty = 'P9999999';
					const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
					statement.references = [];
					statement.references[ 0 ] = {
						parts: [ { property: { id: nonExistentProperty }, value: { type: 'novalue' } } ]
					};

					const response = await newReplaceRequestBuilder( testItemId, testStatementId, statement )
						.assertValidRequest().makeRequest();

					assertValidError(
						response,
						400,
						'referenced-resource-not-found',
						{ path: '/statement/references/0/parts/0/property/id' }
					);
					assert.strictEqual( response.body.message, 'The referenced resource does not exist' );
				} );
			} );

			describe( '404 error response', () => {
				it( 'statement not found on item', async () => {
					const statementId = testItemId + '$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
					const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
					const response = await newReplaceRequestBuilder( testItemId, statementId, statement )
						.assertValidRequest().makeRequest();

					assertValidError( response, 404, 'resource-not-found', { resource_type: 'statement' } );
					assert.strictEqual( response.body.message, 'The requested resource does not exist' );
				} );
			} );
		} );

	} );

	describe( 'long route specific errors', () => {

		it( 'responds 400 for invalid item ID', async () => {
			const itemId = 'X123';
			const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
			const response = await newReplaceItemStatementRequestBuilder( itemId, testStatementId, statement )
				.assertInvalidRequest().makeRequest();

			assertValidError(
				response,
				400,
				'invalid-path-parameter',
				{ parameter: 'item_id' }
			);
		} );

		it( 'responds 400 if subject ID is not an item ID', async () => {
			const itemId = 'P123';
			const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
			const response = await newReplaceItemStatementRequestBuilder( itemId, testStatementId, statement )
				.assertInvalidRequest().makeRequest();

			assertValidError(
				response,
				400,
				'invalid-path-parameter',
				{ parameter: 'item_id' }
			);
		} );

		it( 'responds 404 item-not-found for nonexistent item', async () => {
			const itemId = 'Q9999999';
			const statementId = `${itemId}$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE`;
			const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
			const response = await newReplaceItemStatementRequestBuilder( itemId, statementId )
				.withJsonBodyParam( 'statement', statement )
				.assertValidRequest()
				.makeRequest();

			assertValidError( response, 404, 'resource-not-found', { resource_type: 'item' } );
			assert.strictEqual( response.body.message, 'The requested resource does not exist' );
		} );

		it( 'responds 404 if statement subject is a redirect', async () => {
			const redirectSource = await entityHelper.createRedirectForItem( testItemId );
			const statementId = redirectSource + '$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
			const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
			const response = await newReplaceItemStatementRequestBuilder( redirectSource, statementId )
				.withJsonBodyParam( 'statement', statement )
				.assertValidRequest().makeRequest();

			assertValidError( response, 404, 'resource-not-found', { resource_type: 'statement' } );
			assert.strictEqual( response.body.message, 'The requested resource does not exist' );
		} );

		it( 'responds 400 if item and statement do not match', async () => {
			const itemId = ( await newCreateItemRequestBuilder( {} ).makeRequest() ).body.id;
			const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
			const response = await newReplaceItemStatementRequestBuilder( itemId, testStatementId )
				.withJsonBodyParam( 'statement', statement )
				.assertValidRequest()
				.makeRequest();

			const context = { item_id: itemId, statement_id: testStatementId };
			assertValidError( response, 400, 'item-statement-id-mismatch', context );
		} );

	} );

	describe( 'short route specific errors', () => {
		it( 'responds 400 invalid-statement-id if statement is not on a valid entity', async () => {
			const statementId = testStatementId.replace( 'Q', 'X' );
			const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
			const response = await newReplaceStatementRequestBuilder( statementId, statement )
				.assertInvalidRequest().makeRequest();

			assertValidError(
				response,
				400,
				'invalid-path-parameter',
				{ parameter: 'statement_id' }
			);
		} );

		it( 'responds 404 statement not found for nonexistent item', async () => {
			const statementId = 'Q9999999$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
			const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
			const response = await newReplaceStatementRequestBuilder( statementId )
				.withJsonBodyParam( 'statement', statement )
				.assertValidRequest()
				.makeRequest();

			assertValidError( response, 404, 'resource-not-found', { resource_type: 'statement' } );
			assert.strictEqual( response.body.message, 'The requested resource does not exist' );
		} );

		it( 'responds 404 if statement subject is a redirect', async () => {
			const redirectSource = await entityHelper.createRedirectForItem( testItemId );
			const statementId = redirectSource + '$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
			const statement = entityHelper.newStatementWithRandomStringValue( predicatePropertyId );
			const response = await newReplaceStatementRequestBuilder( statementId )
				.withJsonBodyParam( 'statement', statement )
				.assertValidRequest().makeRequest();

			assertValidError( response, 404, 'resource-not-found', { resource_type: 'statement' } );
			assert.strictEqual( response.body.message, 'The requested resource does not exist' );
		} );
	} );

} );
