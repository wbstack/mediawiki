'use strict';

const { assert, utils, action } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const entityHelper = require( '../helpers/entityHelper' );
const { newPatchSitelinksRequestBuilder, newCreateItemRequestBuilder } = require( '../helpers/RequestBuilderFactory' );
const { createLocalSitelink, getLocalSiteId } = require( '../helpers/entityHelper' );
const { makeEtag } = require( '../helpers/httpHelper' );
const { formatSitelinksEditSummary } = require( '../helpers/formatEditSummaries' );
const testValidatesPatch = require( '../helpers/testValidatesPatch' );
const { getAllowedBadges } = require( '../helpers/getAllowedBadges' );
const { assertValidError } = require( '../helpers/responseValidator' );
const { getOrCreateBotUser } = require( '../helpers/testUsers' );

describe( newPatchSitelinksRequestBuilder().getRouteDescription(), () => {

	let testItemId;
	let siteId;
	const linkedArticle = utils.title( 'Article-linked-to-test-item' );
	let originalLastModified;
	let originalRevisionId;
	let allowedBadges;

	function assertValidResponse( response, status, title, badges ) {
		expect( response ).to.have.status( status );
		assert.strictEqual( response.header[ 'content-type' ], 'application/json' );
		assert.isAbove( new Date( response.header[ 'last-modified' ] ), originalLastModified );
		assert.notStrictEqual( response.header.etag, makeEtag( originalRevisionId ) );
		assert.strictEqual( response.body[ siteId ].title, title );
		assert.deepEqual( response.body[ siteId ].badges, badges );
		assert.include( response.body[ siteId ].url, title );
	}

	before( async function () {
		testItemId = ( await newCreateItemRequestBuilder( {} ).makeRequest() ).body.id;
		await createLocalSitelink( testItemId, linkedArticle );
		siteId = await getLocalSiteId();
		allowedBadges = await getAllowedBadges();

		const testItemCreationMetadata = await entityHelper.getLatestEditMetadata( testItemId );
		originalLastModified = new Date( testItemCreationMetadata.timestamp );
		originalRevisionId = testItemCreationMetadata.revid;

		// wait 1s before next test to ensure the last-modified timestamps are different
		await new Promise( ( resolve ) => {
			setTimeout( resolve, 1000 );
		} );
	} );

	describe( '200 OK', () => {
		it( 'can add a sitelink', async () => {
			const sitelink = { title: linkedArticle, badges: [ allowedBadges[ 0 ] ] };
			const response = await newPatchSitelinksRequestBuilder(
				testItemId,
				[ { op: 'add', path: `/${siteId}`, value: sitelink } ]
			).makeRequest();

			assertValidResponse( response, 200, sitelink.title, sitelink.badges );
		} );

		it( 'can patch sitelinks with edit metadata', async () => {
			const sitelink = { title: linkedArticle, badges: [ allowedBadges[ 1 ] ] };
			const user = await getOrCreateBotUser();
			const tag = await action.makeTag( 'e2e test tag', 'Created during e2e test', true );
			const editSummary = 'I made a patch';

			const response = await newPatchSitelinksRequestBuilder(
				testItemId,
				[ { op: 'add', path: `/${siteId}`, value: sitelink } ]
			).withJsonBodyParam( 'tags', [ tag ] )
				.withJsonBodyParam( 'bot', true )
				.withJsonBodyParam( 'comment', editSummary )
				.withUser( user )
				.assertValidRequest().makeRequest();

			assertValidResponse( response, 200, sitelink.title, sitelink.badges );

			const editMetadata = await entityHelper.getLatestEditMetadata( testItemId );
			assert.include( editMetadata.tags, tag );
			assert.property( editMetadata, 'bot' );
			assert.deepEqual( editMetadata.comment, formatSitelinksEditSummary( editSummary ) );
		} );
	} );

	describe( '400 error response', () => {
		it( 'invalid item id', async () => {
			const itemId = testItemId.replace( 'Q', 'P' );
			const response = await newPatchSitelinksRequestBuilder( itemId, [] )
				.assertInvalidRequest().makeRequest();

			assertValidError(
				response,
				400,
				'invalid-path-parameter',
				{ parameter: 'item_id' }
			);
		} );

		testValidatesPatch( ( patch ) => newPatchSitelinksRequestBuilder( testItemId, patch ) );
	} );

	describe( '404 error response', () => {

		it( 'item not found', async () => {
			const itemId = 'Q999999';
			const response = await newPatchSitelinksRequestBuilder( itemId, [] )
				.assertValidRequest()
				.makeRequest();

			assertValidError( response, 404, 'resource-not-found', { resource_type: 'item' } );
			assert.strictEqual( response.body.message, 'The requested resource does not exist' );
		} );

	} );

	describe( '409 error response', () => {

		it( 'item is a redirect', async () => {
			const redirectTarget = testItemId;
			const redirectSource = await entityHelper.createRedirectForItem( redirectTarget );

			const response = await newPatchSitelinksRequestBuilder( redirectSource, [] )
				.assertValidRequest()
				.makeRequest();

			assertValidError( response, 409, 'redirected-item', { redirect_target: redirectTarget } );
			assert.include( response.body.message, redirectSource );
			assert.include( response.body.message, redirectTarget );
		} );

		it( '"path" field target does not exist', async () => {
			const operation = { op: 'remove', path: '/path/does/not/exist' };

			const response = await newPatchSitelinksRequestBuilder( testItemId, [ operation ] )
				.assertValidRequest()
				.makeRequest();

			const context = { path: '/patch/0/path' };
			assertValidError( response, 409, 'patch-target-not-found', context );
			assert.strictEqual( response.body.message, 'Target not found on resource' );
		} );

		it( '"from" field target does not exist', async () => {
			const operation = { op: 'copy', from: '/path/does/not/exist', path: `/${siteId}` };

			const response = await newPatchSitelinksRequestBuilder( testItemId, [ operation ] )
				.assertValidRequest()
				.makeRequest();

			const context = { path: '/patch/0/from' };
			assertValidError( response, 409, 'patch-target-not-found', context );
			assert.strictEqual( response.body.message, 'Target not found on resource' );
		} );

		it( 'patch test condition failed', async () => {
			const operation = { op: 'test', path: `/${siteId}/title`, value: 'potato' };
			const response = await newPatchSitelinksRequestBuilder( testItemId, [ operation ] )
				.assertValidRequest()
				.makeRequest();

			assertValidError( response, 409, 'patch-test-failed', { path: '/patch/0', actual_value: linkedArticle } );
			assert.strictEqual( response.body.message, 'Test operation in the provided patch failed' );
		} );

	} );

	describe( '422 error response', () => {
		const makeReplaceExistingSitelinkPatchOperation = ( newSitelink ) => ( {
			op: 'replace',
			path: `/${siteId}`,
			value: newSitelink
		} );

		it( 'sitelink is not an object', async () => {
			const invalidSitelinkType = 'not-valid-sitelink-type';

			const response = await newPatchSitelinksRequestBuilder(
				testItemId,
				[ { op: 'add', path: `/${siteId}`, value: invalidSitelinkType } ]
			).assertValidRequest().makeRequest();

			const context = { path: `/${siteId}`, value: invalidSitelinkType };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'sitelinks not an object', async () => {
			const invalidSitelinks = [ { title: linkedArticle } ];

			const response = await newPatchSitelinksRequestBuilder(
				testItemId,
				[ { op: 'add', path: '', value: invalidSitelinks } ]
			).assertValidRequest().makeRequest();

			const context = { path: '', value: invalidSitelinks };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'invalid site id', async () => {
			const invalidSiteId = 'not-valid-site-id';
			const sitelink = { title: linkedArticle, badges: [ allowedBadges[ 0 ] ] };

			const response = await newPatchSitelinksRequestBuilder(
				testItemId,
				[ { op: 'add', path: `/${invalidSiteId}`, value: sitelink } ]
			).assertValidRequest().makeRequest();

			assertValidError( response, 422, 'patch-result-invalid-key', { path: '', key: invalidSiteId } );
		} );

		it( 'missing title', async () => {
			const sitelink = { badges: [ allowedBadges[ 0 ] ] };

			const response = await newPatchSitelinksRequestBuilder(
				testItemId,
				[ makeReplaceExistingSitelinkPatchOperation( sitelink ) ]
			).assertValidRequest().makeRequest();

			const context = { path: `/${siteId}`, field: 'title' };
			assertValidError( response, 422, 'patch-result-missing-field', context );
		} );

		it( 'empty title', async () => {
			const sitelink = { title: '', badges: [ allowedBadges[ 0 ] ] };

			const response = await newPatchSitelinksRequestBuilder(
				testItemId,
				[ makeReplaceExistingSitelinkPatchOperation( sitelink ) ]
			).assertValidRequest().makeRequest();

			const context = { path: `/${siteId}/title`, value: '' };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'invalid title', async () => {
			const title = 'invalid??%00';
			const response = await newPatchSitelinksRequestBuilder(
				testItemId,
				[ makeReplaceExistingSitelinkPatchOperation( { title } ) ]
			).assertValidRequest().makeRequest();

			const context = { path: `/${siteId}/title`, value: title };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'title does not exist', async () => {
			const title = 'this_title_does_not_exist';
			const sitelink = { title, badges: [ allowedBadges[ 0 ] ] };

			const response = await newPatchSitelinksRequestBuilder(
				testItemId,
				[ makeReplaceExistingSitelinkPatchOperation( sitelink ) ]
			).assertValidRequest().makeRequest();

			const context = { path: `/${siteId}/title`, value: title };
			assertValidError( response, 422, 'patch-result-referenced-resource-not-found', context );
			assert.strictEqual( response.body.message, 'The referenced resource does not exist' );
		} );

		it( 'invalid badge', async () => {
			const badge = 'not-an-item-id';
			const sitelink = { title: linkedArticle, badges: [ badge ] };

			const response = await newPatchSitelinksRequestBuilder(
				testItemId,
				[ makeReplaceExistingSitelinkPatchOperation( sitelink ) ]
			).assertValidRequest().makeRequest();

			const context = { path: `/${siteId}/badges/0`, value: badge };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'item not a badge', async () => {
			const notBadgeItemId = 'Q113';
			const sitelink = { title: linkedArticle, badges: [ notBadgeItemId ] };

			const response = await newPatchSitelinksRequestBuilder(
				testItemId,
				[ makeReplaceExistingSitelinkPatchOperation( sitelink ) ]
			).assertValidRequest().makeRequest();

			const context = { path: `/${siteId}/badges/0`, value: notBadgeItemId };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
		} );

		it( 'badges are not a list', async () => {
			const badgesWithInvalidFormat = 'Q113, Q232, Q444';
			const sitelink = { title: linkedArticle, badges: badgesWithInvalidFormat };

			const response = await newPatchSitelinksRequestBuilder(
				testItemId,
				[ makeReplaceExistingSitelinkPatchOperation( sitelink ) ]
			).assertValidRequest().makeRequest();

			const context = { path: `/${siteId}/badges`, value: badgesWithInvalidFormat };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'sitelink conflict', async () => {
			await newPatchSitelinksRequestBuilder(
				testItemId,
				[ { op: 'add', path: `/${siteId}`, value: { title: linkedArticle } } ]
			).assertValidRequest().makeRequest();

			const newItem = await newCreateItemRequestBuilder( {} ).makeRequest();
			const response = await newPatchSitelinksRequestBuilder(
				newItem.body.id,
				[ { op: 'add', path: `/${siteId}`, value: { title: linkedArticle } } ]
			).assertValidRequest().makeRequest();

			const context = {
				violation: 'sitelink-conflict',
				violation_context: { site_id: siteId, conflicting_item_id: testItemId }
			};

			assertValidError( response, 422, 'data-policy-violation', context );
			assert.strictEqual( response.body.message, 'Edit violates data policy' );
		} );

		it( 'url is modified', async () => {
			const response = await newPatchSitelinksRequestBuilder(
				testItemId,
				[
					{
						op: 'add',
						path: `/${siteId}`,
						value: { title: linkedArticle, url: 'https://en.wikipedia.org/wiki/Example.com' }
					}
				]
			).assertValidRequest().makeRequest();

			const path = `/${siteId}/url`;
			assertValidError( response, 422, 'patch-result-modified-read-only-value', { path } );
			assert.strictEqual( response.body.message, 'Read only value in patch result cannot be modified' );
		} );

	} );

} );
