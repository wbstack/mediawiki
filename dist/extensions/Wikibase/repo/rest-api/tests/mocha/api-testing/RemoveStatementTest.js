'use strict';

const { assert, action } = require( 'api-testing' );
const entityHelper = require( '../helpers/entityHelper' );
const { RequestBuilder } = require( '../helpers/RequestBuilder' );

async function addStatementWithRandomStringValue( itemId, propertyId ) {
	const response = await new RequestBuilder()
		.withRoute( 'POST', '/entities/items/{item_id}/statements' )
		.withPathParam( 'item_id', itemId )
		.withHeader( 'content-type', 'application/json' )
		.withJsonBodyParam(
			'statement',
			entityHelper.newStatementWithRandomStringValue( propertyId )
		).makeRequest();

	return response.body;
}

function newRemoveStatementRequestBuilder( statementId ) {
	return new RequestBuilder()
		.withRoute( 'DELETE', '/statements/{statement_id}' )
		.withPathParam( 'statement_id', statementId );
}

describe( 'DELETE /statements/{statement_id}', () => {
	let testItemId;
	let testPropertyId;

	before( async () => {
		testItemId = ( await entityHelper.createEntity( 'item', {} ) ).entity.id;
		testPropertyId = ( await entityHelper.createUniqueStringProperty() ).entity.id;
	} );

	function assertValid200Response( response ) {
		assert.equal( response.status, 200 );
		assert.equal( response.body, 'Statement deleted' );
	}

	async function verifyStatementDeleted( statementId ) {
		const verifyStatement = await new RequestBuilder()
			.withRoute( 'GET', '/statements/{statement_id}' )
			.withPathParam( 'statement_id', statementId )
			.makeRequest();

		assert.equal( verifyStatement.status, 404 );

	}

	describe( '200 success response', () => {
		let testStatement;

		beforeEach( async () => {
			testStatement = await addStatementWithRandomStringValue( testItemId, testPropertyId );
		} );

		it( 'can remove a statement without request body', async () => {
			const response = await newRemoveStatementRequestBuilder( testStatement.id )
				.assertValidRequest()
				.makeRequest();

			assertValid200Response( response );
			const { comment } = await entityHelper.getLatestEditMetadata( testItemId );
			assert.empty( comment );
			await verifyStatementDeleted( testStatement.id );
		} );

		it( 'can remove a statement with edit metadata provided', async () => {
			const user = await action.mindy();
			const tag = await action.makeTag( 'e2e test tag', 'Created during e2e test' );
			const editSummary = 'omg look i removed a statement';
			const response = await newRemoveStatementRequestBuilder( testStatement.id )
				.withJsonBodyParam( 'tags', [ tag ] )
				.withJsonBodyParam( 'bot', true )
				.withJsonBodyParam( 'comment', editSummary )
				.withUser( user )
				.assertValidRequest()
				.makeRequest();

			assertValid200Response( response );
			await verifyStatementDeleted( testStatement.id );

			const editMetadata = await entityHelper.getLatestEditMetadata( testItemId );
			assert.include( editMetadata.tags, tag );
			assert.property( editMetadata, 'bot' );
			assert.strictEqual( editMetadata.comment, editSummary );
			assert.strictEqual( editMetadata.user, user.username );
		} );
	} );

	describe( '400 error response', () => {
		it( 'statement ID contains invalid entity ID', async () => {
			const statementId = 'X123$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
			const response = await newRemoveStatementRequestBuilder( statementId )
				.assertInvalidRequest()
				.makeRequest();

			assert.equal( response.status, 400 );
			assert.header( response, 'Content-Language', 'en' );
			assert.equal( response.body.code, 'invalid-statement-id' );
			assert.include( response.body.message, statementId );
		} );

		it( 'statement ID is invalid format', async () => {
			const statementId = 'not-a-valid-format';
			const response = await newRemoveStatementRequestBuilder( statementId )
				.assertInvalidRequest()
				.makeRequest();

			assert.equal( response.status, 400 );
			assert.header( response, 'Content-Language', 'en' );
			assert.equal( response.body.code, 'invalid-statement-id' );
			assert.include( response.body.message, statementId );
		} );

		it( 'statement is not on an item', async () => {
			const statementId = 'P123$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
			const response = await newRemoveStatementRequestBuilder( statementId )
				.assertValidRequest()
				.makeRequest();

			assert.equal( response.status, 400 );
			assert.header( response, 'Content-Language', 'en' );
			assert.equal( response.body.code, 'invalid-statement-id' );
			assert.include( response.body.message, statementId );
		} );
	} );

	describe( '404 error response', () => {
		it( 'statement not found on item', async () => {
			const statementId = testItemId + '$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
			const response = await newRemoveStatementRequestBuilder( statementId )
				.assertValidRequest()
				.makeRequest();

			assert.equal( response.status, 404 );
			assert.header( response, 'Content-Language', 'en' );
			assert.equal( response.body.code, 'statement-not-found' );
			assert.include( response.body.message, statementId );
		} );
		it( 'item not found', async () => {
			const statementId = 'Q999999$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
			const response = await newRemoveStatementRequestBuilder( statementId )
				.assertValidRequest()
				.makeRequest();

			assert.equal( response.status, 404 );
			assert.header( response, 'Content-Language', 'en' );
			assert.equal( response.body.code, 'statement-not-found' );
			assert.include( response.body.message, statementId );
		} );
	} );

	describe( '415 error response', () => {
		it( 'unsupported media type', async () => {
			const contentType = 'multipart/form-data';
			const response = await newRemoveStatementRequestBuilder( 'id-does-not-matter' )
				.withHeader( 'content-type', contentType )
				.makeRequest();

			assert.strictEqual( response.status, 415 );
			assert.strictEqual( response.body.message, `Unsupported Content-Type: '${contentType}'` );
		} );
	} );
} );
