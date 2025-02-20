'use strict';

const {
	newRemoveSitelinkRequestBuilder,
	newGetSitelinksRequestBuilder,
	newCreateItemRequestBuilder
} = require( '../helpers/RequestBuilderFactory' );
const { action, utils, assert } = require( 'api-testing' );
const { getLocalSiteId, createLocalSitelink } = require( '../helpers/entityHelper' );
const { expect } = require( '../helpers/chaiHelper' );
const entityHelper = require( '../helpers/entityHelper' );
const { assertValidError } = require( '../helpers/responseValidator' );
const { getOrCreateBotUser } = require( '../helpers/testUsers' );

describe( newRemoveSitelinkRequestBuilder().getRouteDescription(), () => {

	let testItemId;
	let siteId;
	const linkedArticle = utils.title( 'Article-linked-to-test-item' );

	before( async () => {
		siteId = await getLocalSiteId();

		const createItemResponse = await newCreateItemRequestBuilder( {} ).makeRequest();
		testItemId = createItemResponse.body.id;

		await createLocalSitelink( testItemId, linkedArticle );
	} );

	describe( '200', () => {
		afterEach( async () => {
			await createLocalSitelink( testItemId, linkedArticle );
		} );

		it( 'can DELETE a single sitelink of an item', async () => {
			const response = await newRemoveSitelinkRequestBuilder( testItemId, siteId )
				.assertValidRequest()
				.makeRequest();

			expect( response ).to.have.status( 200 );
			assert.equal( response.body, 'Sitelink deleted' );
			assert.header( response, 'Content-Language', 'en' );

			const sitelinks = ( await newGetSitelinksRequestBuilder( testItemId ).makeRequest() ).body;
			assert.notProperty( sitelinks, siteId );
		} );

		it( 'can DELETE a sitelink with edit metadata provided', async () => {
			const user = await getOrCreateBotUser();
			const tag = await action.makeTag( 'e2e test tag', 'Created during e2e test', true );
			const comment = 'removed a bad sitelink!';

			const response = await newRemoveSitelinkRequestBuilder( testItemId, siteId )
				.withUser( user )
				.withJsonBodyParam( 'bot', true )
				.withJsonBodyParam( 'tags', [ tag ] )
				.withJsonBodyParam( 'comment', comment )
				.assertValidRequest()
				.makeRequest();

			expect( response ).to.have.status( 200 );
			assert.equal( response.body, 'Sitelink deleted' );
			assert.header( response, 'Content-Language', 'en' );

			const editMetadata = await entityHelper.getLatestEditMetadata( testItemId );
			assert.include( editMetadata.tags, tag );
			assert.property( editMetadata, 'bot' );
			assert.strictEqual(
				editMetadata.comment,
				`/* wbsetsitelink-remove:1|${siteId} */ ${linkedArticle}, ${comment}`
			);
		} );
	} );

	describe( '404', () => {
		it( 'responds 404 if there is no sitelink for the requested site', async () => {
			const itemWithNoSitelink = ( await newCreateItemRequestBuilder( {} ).makeRequest() ).body.id;
			const response = await newRemoveSitelinkRequestBuilder( itemWithNoSitelink, siteId )
				.assertValidRequest()
				.makeRequest();

			assertValidError( response, 404, 'resource-not-found', { resource_type: 'sitelink' } );
			assert.strictEqual( response.body.message, 'The requested resource does not exist' );
		} );

		it( 'responds with 404 if the item does not exist', async () => {
			const itemDoesNotExist = 'Q999999';
			const response = await newRemoveSitelinkRequestBuilder( itemDoesNotExist, siteId )
				.assertValidRequest()
				.makeRequest();

			assertValidError( response, 404, 'resource-not-found', { resource_type: 'item' } );
			assert.strictEqual( response.body.message, 'The requested resource does not exist' );
		} );
	} );

	it( 'responds 409 if the item is a redirect', async () => {
		const redirectTarget = testItemId;
		const redirectSource = await entityHelper.createRedirectForItem( redirectTarget );
		const response = await newRemoveSitelinkRequestBuilder( redirectSource, siteId )
			.assertValidRequest()
			.makeRequest();

		assertValidError( response, 409, 'redirected-item', { redirect_target: redirectTarget } );
		assert.include( response.body.message, redirectSource );
		assert.include( response.body.message, redirectTarget );
	} );

	describe( '400', () => {
		it( 'invalid item ID', async () => {
			const invalidItemId = 'X123';
			const response = await newRemoveSitelinkRequestBuilder( invalidItemId, siteId )
				.assertInvalidRequest()
				.makeRequest();

			assertValidError(
				response,
				400,
				'invalid-path-parameter',
				{ parameter: 'item_id' }
			);
		} );

		it( 'invalid site ID', async () => {
			const response = await newRemoveSitelinkRequestBuilder( testItemId, 'not-a-valid-site-id' )
				// .assertInvalidRequest() - valid per OAS because it only checks whether it is a string
				.makeRequest();

			assertValidError(
				response,
				400,
				'invalid-path-parameter',
				{ parameter: 'site_id' }
			);
		} );
	} );
} );
