import { createLocalVue, shallowMount } from '@vue/test-utils';
import ErrorDeprecatedStatement from '@/presentation/components/ErrorDeprecatedStatement.vue';
import IconMessageBox from '@/presentation/components/IconMessageBox.vue';
import BailoutActions from '@/presentation/components/BailoutActions.vue';
import MessageKeys from '@/definitions/MessageKeys';
import Vuex from 'vuex';
import { calledWithHTMLElement } from '../../../util/assertions';
import { createTestStore } from '../../../util/store';

const localVue = createLocalVue();
localVue.use( Vuex );

describe( 'ErrorDeprecatedStatement', () => {
	const targetProperty = 'P569',
		pageTitle = 'Marie_Curie',
		originalHref = 'https://www.wikidata.org/wiki/Q7186',
		messageGet = jest.fn( ( key ) => key ),
		store = createTestStore( {
			state: {
				targetProperty,
				pageTitle,
				originalHref,
			},
		} );

	it( 'uses IconMessageBox to display the error header and body messages', () => {
		const wrapper = shallowMount( ErrorDeprecatedStatement, {
			localVue,
			mocks: {
				$messages: {
					KEYS: MessageKeys,
					get: messageGet,
				},
			},
			store,
		} );

		calledWithHTMLElement( messageGet, 0, 1 );
		calledWithHTMLElement( messageGet, 1, 1 );

		expect( wrapper.find( IconMessageBox ).exists() ).toBe( true );
		expect( messageGet ).toHaveBeenNthCalledWith(
			1,
			MessageKeys.DEPRECATED_STATEMENT_ERROR_HEAD,
			`<span class="wb-db-term-label" lang="zxx" dir="auto">${targetProperty}</span>`,
		);
		expect( messageGet ).toHaveBeenNthCalledWith(
			2,
			MessageKeys.DEPRECATED_STATEMENT_ERROR_BODY,
			`<span class="wb-db-term-label" lang="zxx" dir="auto">${targetProperty}</span>`,
		);
	} );

	it( 'uses BailoutActions to provide a bail out path for the deprecated statement error', () => {
		const wrapper = shallowMount( ErrorDeprecatedStatement, {
			localVue,
			mocks: {
				$messages: {
					KEYS: MessageKeys,
					get: messageGet,
				},
			},
			store,
		} );

		expect( wrapper.find( BailoutActions ).exists() ).toBe( true );
		expect( wrapper.find( BailoutActions ).props() ).toStrictEqual( {
			originalHref,
			pageTitle,
		} );
	} );

} );
