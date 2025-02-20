'use strict';

const { expect } = require( '../helpers/chaiHelper' );
const { assert, utils } = require( 'api-testing' );
const {
	newPatchPropertyRequestBuilder,
	newAddPropertyStatementRequestBuilder,
	newGetPropertyLabelRequestBuilder,
	newCreatePropertyRequestBuilder
} = require( '../helpers/RequestBuilderFactory' );
const entityHelper = require( '../helpers/entityHelper' );
const { makeEtag } = require( '../helpers/httpHelper' );
const testValidatesPatch = require( '../helpers/testValidatesPatch' );
const { assertValidError } = require( '../helpers/responseValidator' );
const { formatWholeEntityEditSummary } = require( '../helpers/formatEditSummaries' );
const { runAllJobs } = require( 'api-testing/lib/wiki' );

describe( newPatchPropertyRequestBuilder().getRouteDescription(), () => {

	let testPropertyId;
	let originalLastModified;
	let originalRevisionId;
	let predicatePropertyId;
	const languageWithExistingLabel = 'en';
	const languageWithExistingDescription = 'en';
	const existingEnAlias = 'synonym';

	function assertValid200Response( response ) {
		expect( response ).to.have.status( 200 );
		assert.strictEqual( response.body.id, testPropertyId );
		assert.strictEqual( response.header[ 'content-type' ], 'application/json' );
		assert.isAbove( new Date( response.header[ 'last-modified' ] ), originalLastModified );
		assert.notStrictEqual( response.header.etag, makeEtag( originalRevisionId ) );
	}

	before( async function () {
		testPropertyId = ( await newCreatePropertyRequestBuilder( {
			data_type: 'string',
			labels: { [ languageWithExistingLabel ]: `some-label-${utils.uniq()}` },
			descriptions: { [ languageWithExistingDescription ]: `some-description-${utils.uniq()}` },
			aliases: { en: [ existingEnAlias ], fr: [ 'croissant' ] }
		} ).makeRequest() ).body.id;

		const testPropertyCreationMetadata = await entityHelper.getLatestEditMetadata( testPropertyId );
		originalLastModified = new Date( testPropertyCreationMetadata.timestamp );
		originalRevisionId = testPropertyCreationMetadata.revid;

		predicatePropertyId = ( await newCreatePropertyRequestBuilder( { data_type: 'string' } ).makeRequest() ).body.id;

		// wait 1s before next test to ensure the last-modified timestamps are different
		await new Promise( ( resolve ) => {
			setTimeout( resolve, 1000 );
		} );
	} );

	describe( '200 OK', () => {
		it( 'can patch a property', async () => {
			const newLabel = `neues deutsches label ${utils.uniq()}`;
			const updatedDescription = `changed description ${utils.uniq()}`;
			const newStatementValue = 'new statement';
			const editSummary = 'I made a patch';

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[
					{ op: 'add', path: '/labels/de', value: newLabel },
					{ op: 'replace', path: '/descriptions/en', value: updatedDescription },
					{ op: 'add', path: '/aliases/en/-', value: 'new en alias' },
					{ op: 'add', path: '/aliases/en/-', value: existingEnAlias },
					{ op: 'remove', path: '/aliases/fr' },
					{
						op: 'add',
						path: `/statements/${predicatePropertyId}`,
						value: [ {
							property: { id: predicatePropertyId },
							value: { type: 'value', content: newStatementValue }
						} ]
					}
				]
			).withJsonBodyParam( 'comment', editSummary ).makeRequest();

			assertValid200Response( response );
			assert.strictEqual( response.body.labels.de, newLabel );
			assert.strictEqual( response.body.descriptions.en, updatedDescription );
			assert.deepStrictEqual( response.body.aliases, { en: [ existingEnAlias, 'new en alias' ] } );
			assert.strictEqual( response.body.statements[ predicatePropertyId ][ 0 ].value.content, newStatementValue );
			assert.match(
				response.body.statements[ predicatePropertyId ][ 0 ].id,
				// eslint-disable-next-line security/detect-non-literal-regexp
				new RegExp( `^${testPropertyId}\\$[A-Z0-9]{8}(-[A-Z0-9]{4}){3}-[A-Z0-9]{12}$`, 'i' )
			);

			const editMetadata = await entityHelper.getLatestEditMetadata( testPropertyId );
			assert.strictEqual(
				editMetadata.comment,
				formatWholeEntityEditSummary( 'update-languages-and-other-short', 'de, en, fr', editSummary )
			);
		} );

		it( 'can patch other fields even if there is a statement using a deleted property', async () => {
			const propertyToDelete = ( await entityHelper.createUniqueStringProperty() ).body.id;
			await newAddPropertyStatementRequestBuilder(
				testPropertyId,
				{ property: { id: propertyToDelete }, value: { type: 'novalue' } }
			).makeRequest();

			await entityHelper.deleteProperty( propertyToDelete );
			await runAllJobs(); // wait for secondary data to catch up after deletion

			const label = `some-label-${utils.uniq()}`;

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ { op: 'add', path: '/labels/de', value: label } ]
			).assertValidRequest().makeRequest();

			expect( response ).to.have.status( 200 );
			assert.strictEqual( response.body.labels.de, label );
		} );
	} );

	describe( '400 Bad Request', () => {

		it( 'property ID is invalid', async () => {
			const response = await newPatchPropertyRequestBuilder( 'X123', [] )
				.assertInvalidRequest().makeRequest();

			assertValidError(
				response,
				400,
				'invalid-path-parameter',
				{ parameter: 'property_id' }
			);
		} );

		testValidatesPatch( ( patch ) => newPatchPropertyRequestBuilder( testPropertyId, patch ) );
	} );

	describe( '404 error response', () => {
		it( 'property not found', async () => {
			const propertyId = 'P99999';

			const response = await newPatchPropertyRequestBuilder( propertyId, [] )
				.assertValidRequest().makeRequest();

			assertValidError( response, 404, 'resource-not-found', { resource_type: 'property' } );
			assert.strictEqual( response.body.message, 'The requested resource does not exist' );
		} );

	} );

	describe( '409 error response', () => {

		it( '"path" field target does not exist', async () => {
			const operation = { op: 'remove', path: '/path/does/not/exist' };

			const response = await newPatchPropertyRequestBuilder( testPropertyId, [ operation ] )
				.assertValidRequest().makeRequest();

			const context = { path: '/patch/0/path' };
			assertValidError( response, 409, 'patch-target-not-found', context );
			assert.strictEqual( response.body.message, 'Target not found on resource' );
		} );

		it( '"from" field target does not exist', async () => {
			const operation = { op: 'copy', from: '/path/does/not/exist', path: '/labels/en' };

			const response = await newPatchPropertyRequestBuilder( testPropertyId, [ operation ] )
				.assertValidRequest().makeRequest();

			const context = { path: '/patch/0/from' };
			assertValidError( response, 409, 'patch-target-not-found', context );
			assert.strictEqual( response.body.message, 'Target not found on resource' );
		} );

		it( 'patch test condition failed', async () => {
			const operation = { op: 'test', path: '/labels/en', value: 'german-label' };
			const enLabel = ( await newGetPropertyLabelRequestBuilder( testPropertyId, 'en' ).makeRequest() ).body;

			const response = await newPatchPropertyRequestBuilder( testPropertyId, [ operation ] )
				.assertValidRequest().makeRequest();

			assertValidError( response, 409, 'patch-test-failed', { path: '/patch/0', actual_value: enLabel } );
			assert.strictEqual( response.body.message, 'Test operation in the provided patch failed' );
		} );
	} );

	describe( '422 error response', () => {
		it( 'invalid operation change property id', async () => {
			const patch = [
				{ op: 'replace', path: '/id', value: 'P666' }
			];

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			assertValidError( response, 422, 'patch-result-modified-read-only-value', { path: '/id' } );
			assert.strictEqual( response.body.message, 'Read only value in patch result cannot be modified' );
		} );

		it( 'invalid operation change property datatype', async () => {
			const patch = [
				{ op: 'replace', path: '/data_type', value: 'wikibase-item' }
			];

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			assertValidError( response, 422, 'patch-result-modified-read-only-value', { path: '/data_type' } );
			assert.strictEqual( response.body.message, 'Read only value in patch result cannot be modified' );
		} );

		it( 'missing mandatory field', async () => {
			const patch = [ { op: 'remove', path: '/data_type' } ];

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			const context = { path: '', field: 'data_type' };
			assertValidError( response, 422, 'patch-result-missing-field', context );
		} );

		const makeReplaceExistingLabelPatchOp = ( newLabel ) => ( {
			op: 'replace',
			path: `/labels/${languageWithExistingLabel}`,
			value: newLabel
		} );

		it( 'invalid labels type', async () => {
			const invalidLabels = [ 'not', 'an', 'object' ];

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ { op: 'replace', path: '/labels', value: invalidLabels } ]
			).assertValidRequest().makeRequest();

			const context = { path: '/labels', value: invalidLabels };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'invalid label', async () => {
			const invalidLabel = 'tab characters \t not allowed';

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingLabelPatchOp( invalidLabel ) ]
			).assertValidRequest().makeRequest();

			const context = { path: `/labels/${languageWithExistingLabel}`, value: invalidLabel };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'invalid label type', async () => {
			const invalidLabel = { object: 'not allowed' };

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingLabelPatchOp( invalidLabel ) ]
			).assertValidRequest().makeRequest();

			const context = { path: `/labels/${languageWithExistingLabel}`, value: invalidLabel };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'empty label', async () => {
			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingLabelPatchOp( '' ) ]
			).assertValidRequest().makeRequest();

			const context = { path: `/labels/${languageWithExistingLabel}`, value: '' };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
		} );

		it( 'label too long', async () => {
			// this assumes the default value of 250 from Wikibase.default.php is in place and
			// may fail if $wgWBRepoSettings['string-limits']['multilang']['length'] is overwritten
			const maxLength = 250;

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingLabelPatchOp( 'x'.repeat( maxLength + 1 ) ) ]
			).assertValidRequest().makeRequest();

			const context = { path: '/labels/en', limit: maxLength };
			assertValidError( response, 422, 'patch-result-value-too-long', context );
			assert.strictEqual( response.body.message, 'Patched value is too long' );
		} );

		it( 'invalid label language code', async () => {
			const invalidLanguage = 'invalid-language-code';

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ {
					op: 'add',
					path: `/labels/${invalidLanguage}`,
					value: 'potato'
				} ]
			).assertValidRequest().makeRequest();

			assertValidError( response, 422, 'patch-result-invalid-key', { path: '/labels', key: invalidLanguage } );
		} );

		it( 'property with same label already exists', async () => {
			const label = `test-label-${utils.uniq()}`;

			const existingEntityResponse = await newCreatePropertyRequestBuilder( {
				data_type: 'string', labels: { [ languageWithExistingLabel ]: label } }
			).makeRequest();
			const existingPropertyId = existingEntityResponse.body.id;

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingLabelPatchOp( label ) ]
			).assertValidRequest().makeRequest();

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

		const makeReplaceExistingDescriptionPatchOperation = ( newDescription ) => ( {
			op: 'replace',
			path: `/descriptions/${languageWithExistingDescription}`,
			value: newDescription
		} );

		it( 'invalid descriptions type', async () => {
			const invalidDescriptions = [ 'not', 'an', 'object' ];

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ { op: 'replace', path: '/descriptions', value: invalidDescriptions } ]
			).assertValidRequest().makeRequest();

			const context = { path: '/descriptions', value: invalidDescriptions };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'invalid description', async () => {
			const invalidDescription = 'tab characters \t not allowed';

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingDescriptionPatchOperation( invalidDescription ) ]
			).assertValidRequest().makeRequest();

			const context = { path: `/descriptions/${languageWithExistingDescription}`, value: invalidDescription };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'invalid description type', async () => {
			const invalidDescription = { object: 'not allowed' };

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingDescriptionPatchOperation( invalidDescription ) ]
			).assertValidRequest().makeRequest();

			assertValidError(
				response,
				422,
				'patch-result-invalid-value',
				{ path: `/descriptions/${languageWithExistingDescription}`, value: invalidDescription }
			);
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'empty description', async () => {
			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingDescriptionPatchOperation( '' ) ]
			).assertValidRequest().makeRequest();

			const context = { path: `/descriptions/${languageWithExistingDescription}`, value: '' };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
		} );

		it( 'empty description after trimming whitespace in the input', async () => {
			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingDescriptionPatchOperation( ' \t ' ) ]
			).assertValidRequest().makeRequest();

			const context = { path: `/descriptions/${languageWithExistingDescription}`, value: '' };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
		} );

		it( 'description too long', async () => {
			// this assumes the default value of 250 from Wikibase.default.php is in place and
			// may fail if $wgWBRepoSettings['string-limits']['multilang']['length'] is overwritten
			const maxLength = 250;

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ makeReplaceExistingDescriptionPatchOperation( 'x'.repeat( maxLength + 1 ) ) ]
			).assertValidRequest().makeRequest();

			const context = { path: '/descriptions/en', limit: maxLength };
			assertValidError( response, 422, 'patch-result-value-too-long', context );
			assert.strictEqual( response.body.message, 'Patched value is too long' );
		} );

		it( 'invalid description language code', async () => {
			const invalidLanguage = 'invalid-language-code';

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[ { op: 'add', path: `/descriptions/${invalidLanguage}`, value: 'potato' } ]
			).assertValidRequest().makeRequest();

			assertValidError( response, 422, 'patch-result-invalid-key', { path: '/descriptions', key: invalidLanguage } );
		} );

		it( 'label-description-same-value', async () => {
			const language = languageWithExistingLabel;
			const text = `label-and-description-text-${utils.uniq()}`;

			const response = await newPatchPropertyRequestBuilder(
				testPropertyId,
				[
					{ op: 'replace', path: '/labels/en', value: text },
					{ op: 'replace', path: '/descriptions/en', value: text }
				]
			).assertValidRequest().makeRequest();

			assertValidError(
				response,
				422,
				'data-policy-violation',
				{ violation: 'label-description-same-value', violation_context: { language } }
			);
			assert.strictEqual( response.body.message, 'Edit violates data policy' );
		} );

		it( 'empty alias', async () => {
			const language = 'de';

			const response = await newPatchPropertyRequestBuilder( testPropertyId, [
				{ op: 'add', path: `/aliases/${language}`, value: [ '' ] }
			] ).assertValidRequest().makeRequest();

			const context = { path: `/aliases/${language}/0`, value: '' };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'alias too long', async () => {
			const language = 'de';
			// this assumes the default value of 250 from Wikibase.default.php is in place and
			// may fail if $wgWBRepoSettings['string-limits']['multilang']['length'] is overwritten
			const maxLength = 250;

			const response = await newPatchPropertyRequestBuilder( testPropertyId, [
				{ op: 'add', path: `/aliases/${language}`, value: [ 'x'.repeat( maxLength + 1 ) ] }
			] ).assertValidRequest().makeRequest();

			const context = { path: `/aliases/${language}/0`, limit: maxLength };
			assertValidError( response, 422, 'patch-result-value-too-long', context );
			assert.strictEqual( response.body.message, 'Patched value is too long' );
		} );

		it( 'aliases in language not a list', async () => {
			const language = 'en';
			const invalidAliasesValue = { 'aliases in language': 'not a list' };

			const response = await newPatchPropertyRequestBuilder( testPropertyId, [
				{ op: 'add', path: `/aliases/${language}`, value: invalidAliasesValue }
			] ).assertValidRequest().makeRequest();

			assertValidError(
				response,
				422,
				'patch-result-invalid-value',
				{ path: `/aliases/${language}`, value: invalidAliasesValue }
			);
		} );

		it( 'aliases is not an object', async () => {
			const invalidAliases = [ 'not', 'an', 'object' ];

			const response = await newPatchPropertyRequestBuilder( testPropertyId, [
				{ op: 'add', path: '/aliases', value: invalidAliases }
			] ).assertValidRequest().makeRequest();

			assertValidError( response, 422, 'patch-result-invalid-value', { path: '/aliases', value: invalidAliases } );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'alias contains invalid characters', async () => {
			const language = 'en';
			const invalidAlias = 'tab\t tab\t tab';

			const response = await newPatchPropertyRequestBuilder( testPropertyId, [
				{ op: 'add', path: `/aliases/${language}`, value: [ invalidAlias ] }
			] ).assertValidRequest().makeRequest();

			assertValidError(
				response,
				422,
				'patch-result-invalid-value',
				{ path: `/aliases/${language}/0`, value: invalidAlias }
			);
		} );

		it( 'invalid aliases language code', async () => {
			const invalidLanguage = 'not-a-valid-language';

			const response = await newPatchPropertyRequestBuilder( testPropertyId, [
				{ op: 'add', path: `/aliases/${invalidLanguage}`, value: [ 'alias' ] }
			] ).assertValidRequest().makeRequest();

			assertValidError( response, 422, 'patch-result-invalid-key', { path: '/aliases', key: invalidLanguage } );
		} );

		function makeStatementPatchOperation( propertyId, invalidStatement ) {
			return [ {
				op: 'add',
				path: '/statements',
				value: { [ propertyId ]: [ invalidStatement ] }
			} ];
		}

		it( 'invalid statement group type', async () => {
			const validStatement = {
				property: { id: predicatePropertyId },
				value: { type: 'value', content: 'some-value' }
			};
			const invalidStatementGroupType = { [ predicatePropertyId ]: validStatement };
			const patch = [ { op: 'add', path: '/statements', value: invalidStatementGroupType } ];

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			const context = { path: `/statements/${predicatePropertyId}`, value: validStatement };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );

		} );

		it( 'invalid statement type', async () => {
			const invalidStatement = [ {
				property: { id: predicatePropertyId },
				value: { type: 'value', content: 'some-value' }
			} ];
			const patch = makeStatementPatchOperation( predicatePropertyId, invalidStatement );

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			const context = { path: `/statements/${predicatePropertyId}/0`, value: invalidStatement };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'invalid statements type', async () => {
			const invalidStatements = [ 'invalid statements type' ];
			const patch = [ { op: 'add', path: '/statements', value: invalidStatements } ];

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			const context = { path: '/statements', value: invalidStatements };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'invalid statement field', async () => {
			const invalidRankValue = 'invalid rank';
			const invalidStatement = { rank: invalidRankValue };
			const patch = makeStatementPatchOperation( predicatePropertyId, invalidStatement );

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			const context = { path: `/statements/${predicatePropertyId}/0/rank`, value: invalidRankValue };
			assertValidError( response, 422, 'patch-result-invalid-value', context );
			assert.strictEqual( response.body.message, 'Invalid value in patch result' );
		} );

		it( 'missing statement field', async () => {
			const invalidStatement = { value: { type: 'somevalue', content: 'some-content' } };

			const patch = makeStatementPatchOperation( predicatePropertyId, invalidStatement );

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			const context = { path: `/statements/${predicatePropertyId}/0`, field: 'property' };
			assertValidError( response, 422, 'patch-result-missing-field', context );
		} );

		it( 'statement property id mismatch', async () => {
			const propertyIdKey = 'P123';
			const validStatement = {
				property: { id: predicatePropertyId },
				value: { type: 'value', content: 'some-value' }
			};
			const patch = makeStatementPatchOperation( propertyIdKey, validStatement );

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			const context = {
				path: `/statements/${propertyIdKey}/0/property/id`,
				statement_group_property_id: propertyIdKey,
				statement_property_id: predicatePropertyId
			};
			assertValidError( response, 422, 'patched-statement-group-property-id-mismatch', context );
			assert.strictEqual(
				response.body.message,
				"Statement's Property ID does not match the Statement group key"
			);
		} );

		it( 'statement IDs not modifiable or provided for new statements', async () => {
			const invalidStatement = {
				id: 'P123$4YY2B0D8-BEC1-4D30-B88E-347E08AFD987',
				property: { id: predicatePropertyId },
				value: { type: 'value', content: 'some-value' }
			};
			const patch = makeStatementPatchOperation( predicatePropertyId, invalidStatement );

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			const context = { path: `/statements/${predicatePropertyId}/0/id` };
			assertValidError( response, 422, 'patch-result-modified-read-only-value', context );
			assert.strictEqual( response.body.message, 'Read only value in patch result cannot be modified' );
		} );

		it( 'duplicate Statement id', async () => {
			const duplicateStatement = {
				id: 'P123$4YY2B0D8-BEC1-4D30-B88E-347E08AFD987',
				property: { id: predicatePropertyId },
				value: { type: 'value', content: 'some-value' }
			};
			const invalidStatementGroup = [ duplicateStatement, duplicateStatement ];
			const patch = [ {
				op: 'add',
				path: '/statements',
				value: { [ predicatePropertyId ]: invalidStatementGroup }
			} ];

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			const context = { path: `/statements/${predicatePropertyId}/0/id` };
			assertValidError( response, 422, 'patch-result-modified-read-only-value', context );
			assert.strictEqual( response.body.message, 'Read only value in patch result cannot be modified' );
		} );

		it( 'property IDs modified', async () => {
			const newPropertyId = ( await entityHelper.createUniqueStringProperty() ).body.id;
			const existingStatementsId = ( await newAddPropertyStatementRequestBuilder( testPropertyId, {
				property: { id: predicatePropertyId },
				value: { type: 'novalue' }
			} ).makeRequest() ).body.id;
			const invalidStatement = {
				id: existingStatementsId,
				property: { id: newPropertyId },
				value: { type: 'value', content: 'some-value' }
			};

			const patch = makeStatementPatchOperation( newPropertyId, invalidStatement );

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			const context = { path: `/statements/${newPropertyId}/0/property/id` };
			assertValidError( response, 422, 'patch-result-modified-read-only-value', context );
			assert.strictEqual( response.body.message, 'Read only value in patch result cannot be modified' );
		} );

		it( 'rejects statement with non-existent property', async () => {
			const nonExistentProperty = 'P9999999';
			const patch = [ {
				op: 'add',
				path: `/statements/${nonExistentProperty}`,
				value: [ { property: { id: nonExistentProperty }, value: { type: 'novalue' } } ]
			} ];

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			assertValidError(
				response,
				422,
				'patch-result-referenced-resource-not-found',
				{ path: `/statements/${nonExistentProperty}/0/property/id`, value: nonExistentProperty }
			);
			assert.strictEqual( response.body.message, 'The referenced resource does not exist' );
		} );

		it( 'rejects qualifier with non-existent property', async () => {
			await newAddPropertyStatementRequestBuilder( testPropertyId, {
				property: { id: predicatePropertyId },
				value: { type: 'novalue' }
			} ).makeRequest();

			const nonExistentProperty = 'P9999999';
			const patch = [ {
				op: 'add',
				path: `/statements/${predicatePropertyId}/0/qualifiers`,
				value: [ { property: { id: nonExistentProperty }, value: { type: 'novalue' } } ]
			} ];

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			assertValidError(
				response,
				422,
				'patch-result-referenced-resource-not-found',
				{ path: `/statements/${predicatePropertyId}/0/qualifiers/0/property/id`, value: nonExistentProperty }
			);
			assert.strictEqual( response.body.message, 'The referenced resource does not exist' );
		} );

		it( 'rejects reference with non-existent property', async () => {
			await newAddPropertyStatementRequestBuilder( testPropertyId, {
				property: { id: predicatePropertyId },
				value: { type: 'novalue' }
			} ).makeRequest();

			const nonExistentProperty = 'P9999999';
			const patch = [ {
				op: 'add',
				path: `/statements/${predicatePropertyId}/0/references/0`,
				value: { parts: [ { property: { id: nonExistentProperty }, value: { type: 'novalue' } } ] }
			} ];

			const response = await newPatchPropertyRequestBuilder( testPropertyId, patch )
				.assertValidRequest().makeRequest();

			assertValidError(
				response,
				422,
				'patch-result-referenced-resource-not-found',
				{ path: `/statements/${predicatePropertyId}/0/references/0/parts/0/property/id`, value: nonExistentProperty }
			);
			assert.strictEqual( response.body.message, 'The referenced resource does not exist' );
		} );

	} );
} );
