import MessageKeys from '@/definitions/MessageKeys';
import EventEmittingButton from '@/presentation/components/EventEmittingButton.vue';
import WarningAnonymousEdit from '@/presentation/components/WarningAnonymousEdit.vue';
import { shallowMount, config } from '@vue/test-utils';

config.renderStubDefaultSlot = true;

describe( 'WarningAnonymousEdit', () => {
	it( 'matches the snapshot', () => {
		const $messages = {
			KEYS: MessageKeys,
			get: jest.fn().mockReturnValue( 'Some <abbr>HTML</abbr>.' ),
			getText: jest.fn().mockReturnValue( "Some 'text" ),
		};
		const wrapper = shallowMount( WarningAnonymousEdit, {
			propsData: {
				loginUrl: 'https://data-bridge.test/Login',
			},
			global: {
				mocks: { $messages },
			},
		} );

		expect( wrapper.element ).toMatchSnapshot();
	} );

	it( 'maps proceed button click to proceed event', async () => {
		const wrapper = shallowMount( WarningAnonymousEdit, {
			propsData: {
				loginUrl: 'https://data-bridge.test/Login',
			},
		} );

		wrapper.findComponent( EventEmittingButton ).vm.$emit( 'click' );
		expect( wrapper.emitted( 'proceed' ) ).toHaveLength( 1 );
	} );
} );
