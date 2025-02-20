'use strict';

const { assert, utils } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const {
	createRedirectForItem,
	getLatestEditMetadata,
	newStatementWithRandomStringValue,
	createUniqueStringProperty,
	getLocalSiteId,
	createLocalSitelink
} = require( '../helpers/entityHelper' );
const { newGetItemRequestBuilder, newCreateItemRequestBuilder } = require( '../helpers/RequestBuilderFactory' );
const { makeEtag } = require( '../helpers/httpHelper' );
const { assertValidError } = require( '../helpers/responseValidator' );

const germanLabel = 'a-German-label-' + utils.uniq();
const englishLabel = 'an-English-label-' + utils.uniq();
const englishDescription = 'an-English-description-' + utils.uniq();

describe( newGetItemRequestBuilder().getRouteDescription(), () => {
	let testItemId;
	let testModified;
	let testRevisionId;
	let siteId;
	let testStatementPropertyId;
	let testStatement;
	const linkedArticle = utils.title( 'Article-linked-to-test-item' );

	function newValidRequestBuilderWithTestItem() {
		return newGetItemRequestBuilder( testItemId ).assertValidRequest();
	}

	before( async () => {
		siteId = await getLocalSiteId();

		testStatementPropertyId = ( await createUniqueStringProperty() ).body.id;
		testStatement = newStatementWithRandomStringValue( testStatementPropertyId );

		const createItemResponse = await newCreateItemRequestBuilder( {
			labels: {
				de: germanLabel,
				en: englishLabel
			},
			descriptions: { en: englishDescription },
			statements: { [ testStatementPropertyId ]: [ testStatement ] }
		} ).makeRequest();
		testItemId = createItemResponse.body.id;
		await createLocalSitelink( testItemId, linkedArticle );

		const testItemCreationMetadata = await getLatestEditMetadata( testItemId );
		testModified = testItemCreationMetadata.timestamp;
		testRevisionId = testItemCreationMetadata.revid;
	} );

	it( 'can GET all item data including metadata', async () => {
		const response = await newValidRequestBuilderWithTestItem().makeRequest();

		expect( response ).to.have.status( 200 );

		assert.equal( response.body.id, testItemId );
		assert.deepEqual( response.body.aliases, {} ); // expect {}, not []
		assert.deepEqual( response.body.labels, {
			de: germanLabel,
			en: englishLabel
		} );
		assert.deepEqual( response.body.descriptions, { en: englishDescription } );

		assert.deepEqual(
			response.body.statements[ testStatementPropertyId ][ 0 ].value,
			testStatement.value
		);

		assert.include( response.body.sitelinks[ siteId ].url, linkedArticle );

		assert.equal( response.header[ 'last-modified' ], testModified );
		assert.equal( response.header.etag, makeEtag( testRevisionId ) );
	} );

	it( 'can GET a partial item with single _fields param', async () => {
		const response = await newValidRequestBuilderWithTestItem()
			.withQueryParam( '_fields', 'labels' )
			.makeRequest();

		expect( response ).to.have.status( 200 );
		assert.deepEqual( response.body, {
			id: testItemId,
			labels: {
				de: germanLabel,
				en: englishLabel
			}
		} );
		assert.equal( response.header[ 'last-modified' ], testModified );
		assert.equal( response.header.etag, makeEtag( testRevisionId ) );
	} );

	it( 'can GET a partial item with multiple _fields params', async () => {
		const response = await newValidRequestBuilderWithTestItem()
			.withQueryParam( '_fields', 'labels,descriptions,aliases' )
			.makeRequest();

		expect( response ).to.have.status( 200 );
		assert.deepEqual( response.body, {
			id: testItemId,
			labels: {
				de: germanLabel,
				en: englishLabel
			},
			descriptions: {
				en: englishDescription
			},
			aliases: {} // expect {}, not []
		} );
		assert.equal( response.header[ 'last-modified' ], testModified );
		assert.equal( response.header.etag, makeEtag( testRevisionId ) );
	} );

	it( '400 error - bad request, invalid item ID', async () => {
		const itemId = 'X123';
		const response = await newGetItemRequestBuilder( itemId ).assertInvalidRequest().makeRequest();

		assertValidError(
			response,
			400,
			'invalid-path-parameter',
			{ parameter: 'item_id' }
		);
	} );

	it( '400 error - bad request, invalid field', async () => {
		const queryParamName = '_fields';
		const response = await newGetItemRequestBuilder( 'Q123' )
			.withQueryParam( queryParamName, 'unknown_field' )
			.assertInvalidRequest()
			.makeRequest();

		assertValidError( response, 400, 'invalid-query-parameter', { parameter: queryParamName } );
		assert.include( response.body.message, queryParamName );
	} );

	it( '404 error - item not found', async () => {
		const itemId = 'Q999999';
		const response = await newGetItemRequestBuilder( itemId ).makeRequest();

		assertValidError( response, 404, 'resource-not-found', { resource_type: 'item' } );
		assert.strictEqual( response.body.message, 'The requested resource does not exist' );
	} );

	describe( 'redirects', () => {
		let redirectSourceId;

		before( async () => {
			redirectSourceId = await createRedirectForItem( testItemId );
		} );

		it( 'responds with a 308 including the redirect target location', async () => {
			const response = await newGetItemRequestBuilder( redirectSourceId ).makeRequest();

			expect( response ).to.have.status( 308 );

			const redirectLocation = new URL( response.headers.location );
			assert.isTrue( redirectLocation.pathname.endsWith( `rest.php/wikibase/v1/entities/items/${testItemId}` ) );
			assert.empty( redirectLocation.search );
		} );

		it( 'keeps the original fields param in the Location header', async () => {
			const fields = 'labels,statements';
			const response = await newGetItemRequestBuilder( redirectSourceId )
				.withQueryParam( '_fields', fields )
				.makeRequest();

			expect( response ).to.have.status( 308 );

			const redirectLocation = new URL( response.headers.location );
			assert.equal( redirectLocation.searchParams.get( '_fields' ), fields );
		} );

	} );

} );
