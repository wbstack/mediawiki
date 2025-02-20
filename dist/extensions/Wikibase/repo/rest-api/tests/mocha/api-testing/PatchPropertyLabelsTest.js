'use strict';

const { assert, utils, action } = require( 'api-testing' );
const { expect } = require( '../helpers/chaiHelper' );
const entityHelper = require( '../helpers/entityHelper' );
const {
	newPatchPropertyLabelsRequestBuilder,
	newGetPropertyLabelRequestBuilder,
	newCreatePropertyRequestBuilder
} = require( '../helpers/RequestBuilderFactory' );
const { makeEtag } = require( '../helpers/httpHelper' );
const { formatTermsEditSummary } = require( '../helpers/formatEditSummaries' );
const testValidatesPatch = require( '../helpers/testValidatesPatch' );
const { assertValidError } = require( '../helpers/responseValidator' );
const { getOrCreateBotUser } = require( '../helpers/testUsers' );

describe( newPatchPropertyLabelsRequestBuilder().getRouteDescription(), () => {

	let testPropertyId;
	let originalLastModified;
	let originalRevisionId;
	const languageWithExistingLabel = 'en';

	function assertValid200Response( response ) {
		expect( response ).to.have.status( 200 );
		assert.strictEqual( response.header[ 'content-type' ], 'application/json' );
		assert.isAbove( new Date( response.header[ 'last-modified' ] ), originalLastModified );
		assert.notStrictEqual( response.header.etag, makeEtag( originalRevisionId ) );
	}

	before( async function () {
		testPropertyId = ( await newCreatePropertyRequestBuilder( {
			data_type: 'string',
			labels: { [ languageWithExistingLabel ]: `some-label-${utils.uniq()}` }
		} ).makeRequest() ).body.id;

		const testPropertyCreationMetadata = await entityHelper.getLatestEditMetadata( testPropertyId );
		originalLastModified = new Date( testPropertyCreationMetadata.timestamp );
		originalRevisionId = testPropertyCreationMetadata.revid;

		// wait 1s before next test to ensure the last-modified timestamps are different
		await new Promise( ( resolve ) => {
			setTimeout( resolve, 1000 );
		} );
	} );

	describe( '200 OK', () => {
		it( 'can add a label', async () => {
			const label = `neues deutsches label ${utils.uniq()}`;
			const response = await newPatchPropertyLabelsRequestBuilder(
				testPropertyId,
				[
					{ op: 'add', path: '/de', value: label }
				]
			).makeRequest();

			assertValid200Response( response );
			assert.strictEqual( response.body.de, label );
		} );

		it( 'can patch labels with edit metadata', async () => {
			const label = `new arabic label ${utils.uniq()}`;
			const user = await getOrCreateBotUser();
			const tag = await action.makeTag( 'e2e test tag', 'Created during e2e test', true );
			const editSummary = 'I made a patch';
			const response = await newPatchPropertyLabelsRequestBuilder(
				testPropertyId,
				[
					{
						op: 'add',
						path: '/ar',
						value: label
					}
				]
			).withJsonBodyParam( 'tags', [ tag ] )
				.withJsonBodyParam( 'bot', true )
				.withJsonBodyParam( 'comment', editSummary )
				.withUser( user )
				.assertValidRequest().makeRequest();

			assertValid200Response( response );
			assert.strictEqual( response.body.ar, label );

			const editMetadata = await entityHelper.getLatestEditMetadata( testPropertyId );
			assert.include( editMetadata.tags, tag );
			assert.property( editMetadata, 'bot' );
			assert.strictEqual(
				editMetadata.comment,
				formatTermsEditSummary( 'update-languages-short', 'ar', editSummary )
			);
		} );
	} );

	describe( '400 error response', () => {
		it( 'invalid property id', async () => {
			const response = await newPatchPropertyLabelsRequestBuilder( testPropertyId.replace( 'P', 'L' ), [] )
				.assertInvalidRequest().makeRequest();

			assertValidError(
				response,
				400,
				'invalid-path-parameter',
				{ parameter: 'property_id' }
			);
		} );

		testValidatesPatch( ( patch ) => newPatchPropertyLabelsRequestBuilder( testPropertyId, patch ) );
	} );

	describe( '404 error response', () => {
		it( 'property not found', async () => {
			const propertyId = 'P99999';
			const response = await newPatchPropertyLabelsRequestBuilder(
				propertyId,
				[
					{
						op: 'replace',
						path: `/${languageWithExistingLabel}`,
						value: utils.uniq()
					}
				]
			).assertValidRequest().makeRequest();

			assertValidError( response, 404, 'resource-not-found', { resource_type: 'property' } );
			assert.strictEqual( response.body.message, 'The requested resource does not exist' );
		} );
	} );

	describe( '409 error response', () => {
		it( '"path" field target does not exist', async () => {
			const operation = { op: 'remove', path: '/path/does/not/exist' };

			const response = await newPatchPropertyLabelsRequestBuilder( testPropertyId, [ operation ] )
				.assertValidRequest().makeRequest();

			const context = { path: '/patch/0/path' };
			assertValidError( response, 409, 'patch-target-not-found', context );
			assert.strictEqual( response.body.message, 'Target not found on resource' );
		} );

		it( '"from" field target does not exist', async () => {
			const operation = { op: 'copy', from: '/path/does/not/exist', path: `/${languageWithExistingLabel}` };

			const response = await newPatchPropertyLabelsRequestBuilder( testPropertyId, [ operation ] )
				.assertValidRequest().makeRequest();

			const context = { path: '/patch/0/from' };
			assertValidError( response, 409, 'patch-target-not-found', context );
			assert.strictEqual( response.body.message, 'Target not found on resource' );
		} );

		it( 'patch test condition failed', async () => {
			const operation = { op: 'test', path: `/${languageWithExistingLabel}`, value: 'instance of' };
			const enLabel = ( await newGetPropertyLabelRequestBuilder( testPropertyId, 'en' ).makeRequest() ).body;

			const response = await newPatchPropertyLabelsRequestBuilder( testPropertyId, [ operation ] )
				.assertValidRequest().makeRequest();

			assertValidError( response, 409, 'patch-test-failed', { path: '/patch/0', actual_value: enLabel } );
			assert.strictEqual( response.body.message, 'Test operation in the provided patch failed' );
		} );
	} );

	describe( '422 error response', () => {
		const makeReplaceExistingLabelPatchOp = ( newLabel ) => ( {
			op: 'replace',
			path: `/${languageWithExistingLabel}`,
			value: newLabel
		} );

		it( 'invalid labels type', async () => {
			const invalidLabels = 'foo';
			const response = await newPatchPropertyLabelsRequestBuilder(
				testPropertyId,
				[ { op: 'replace', path: '', value: invalidLabels } ]
			)
				.assertValidRequest()
				.makeRequest();

			const context = { path: '', value: invalidLabels };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'labels is not an object', async () => {
			const invalidLabels = [ 'list, not an object' ];
			const response = await newPatchPropertyLabelsRequestBuilder(
				testPropertyId,
				[ { op: 'replace', path: '', value: invalidLabels } ]
			)
				.assertValidRequest()
				.makeRequest();

			const context = { path: '', value: invalidLabels };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'invalid label', async () => {
			const invalidLabel = 'tab characters \t not allowed';
			const response = await newPatchPropertyLabelsRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingLabelPatchOp( invalidLabel ) ]
			)
				.assertValidRequest()
				.makeRequest();

			const context = { path: `/${languageWithExistingLabel}`, value: invalidLabel };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'invalid label type', async () => {
			const invalidLabel = { object: 'not allowed' };
			const response = await newPatchPropertyLabelsRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingLabelPatchOp( invalidLabel ) ]
			)
				.assertValidRequest()
				.makeRequest();

			const context = { path: `/${languageWithExistingLabel}`, value: invalidLabel };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'empty label', async () => {
			const response = await newPatchPropertyLabelsRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingLabelPatchOp( '' ) ]
			)
				.assertValidRequest()
				.makeRequest();

			const context = { path: `/${languageWithExistingLabel}`, value: '' };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
		} );

		it( 'label too long', async () => {
			// this assumes the default value of 250 from Wikibase.default.php is in place and
			// may fail if $wgWBRepoSettings['string-limits']['multilang']['length'] is overwritten
			const maxLength = 250;
			const comment = 'Label too long';
			const response = await newPatchPropertyLabelsRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingLabelPatchOp( 'x'.repeat( maxLength + 1 ) ) ]
			)
				.withJsonBodyParam( 'comment', comment )
				.assertValidRequest()
				.makeRequest();

			const context = { path: '/en', limit: maxLength };
			assertValidError( response, 422, 'patch-result-value-too-long', context );
			assert.strictEqual( response.body.message, 'Patched value is too long' );
		} );

		it( 'invalid language code', async () => {
			const invalidLanguage = 'invalid-language-code';
			const response = await newPatchPropertyLabelsRequestBuilder(
				testPropertyId,
				[ {
					op: 'add',
					path: `/${invalidLanguage}`,
					value: 'potato'
				} ]
			)
				.assertValidRequest()
				.makeRequest();

			assertValidError( response, 422, 'patch-result-invalid-key', { path: '', key: invalidLanguage } );
		} );

		it( 'label-description-same-value', async () => {
			const language = languageWithExistingLabel;
			const descriptionText = `description-text-${utils.uniq()}`;

			const createEntityResponse = await newCreatePropertyRequestBuilder( {
				data_type: 'string',
				labels: { [ language ]: `label-text-${utils.uniq()}` },
				descriptions: { [ language ]: descriptionText }
			} ).makeRequest();
			testPropertyId = createEntityResponse.body.id;

			const response = await newPatchPropertyLabelsRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingLabelPatchOp( descriptionText ) ]
			)
				.assertValidRequest()
				.makeRequest();

			assertValidError(
				response,
				422,
				'data-policy-violation',
				{ violation: 'label-description-same-value', violation_context: { language } }
			);
			assert.strictEqual( response.body.message, 'Edit violates data policy' );
		} );

		it( 'property with same label already exists', async () => {
			const label = `test-label-${utils.uniq()}`;

			const existingEntityResponse = await newCreatePropertyRequestBuilder( {
				data_type: 'string',
				labels: { [ languageWithExistingLabel ]: label }
			} ).makeRequest();
			const existingPropertyId = existingEntityResponse.body.id;

			const createEntityResponse = await newCreatePropertyRequestBuilder( {
				data_type: 'string',
				labels: { [ languageWithExistingLabel ]: `label-to-be-replaced-${utils.uniq()}` }
			} ).makeRequest();
			testPropertyId = createEntityResponse.body.id;

			const response = await newPatchPropertyLabelsRequestBuilder(
				testPropertyId,
				[ {
					op: 'replace',
					path: `/${languageWithExistingLabel}`,
					value: label
				} ]
			)
				.assertValidRequest().makeRequest();

			const context = {
				violation: 'property-label-duplicate',
				violation_context: {
					language: languageWithExistingLabel,
					conflicting_property_id: existingPropertyId
				}
			};
			assertValidError( response, 422, 'data-policy-violation', context );
			assert.strictEqual( response.body.message, 'Edit violates data policy' );
		} );
	} );

} );
