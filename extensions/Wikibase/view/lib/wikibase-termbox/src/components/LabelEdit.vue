<template>
	<ResizingTextField
		class="wb-ui-label-edit"
		:class="{
			'wb-ui-label-edit--primary': isPrimary,
		}"
		:placeholder="message( MESSAGE_KEYS.PLACEHOLDER_EDIT_LABEL )"
		v-inlanguage="languageCode"
		v-model="value"
		:maxlength="config.textFieldCharacterLimit"
		autocapitalize="off"
	/>
</template>

<script lang="ts">
import Component, { mixins } from 'vue-class-component';
import { NS_ENTITY } from '@/store/namespaces';
import Messages from '@/components/mixins/Messages';
import { Prop } from 'vue-property-decorator';
import { namespace } from 'vuex-class';
import Term from '@/datamodel/Term';
import { ENTITY_LABEL_EDIT } from '@/store/entity/actionTypes';
import { ResizingTextField } from '@wmde/wikibase-vuejs-components';

@Component( {
	components: { ResizingTextField },
} )
export default class LabelEdit extends mixins( Messages ) {
	@Prop( { required: true } )
	public label!: Term|null;

	@Prop( { required: true, type: String } )
	public languageCode!: string;

	@Prop( { required: false, default: false, type: Boolean } )
	public isPrimary!: boolean;

	@namespace( NS_ENTITY ).Action( ENTITY_LABEL_EDIT )
	public editLabel!: ( value: Term ) => void;

	public get value(): string {
		if ( !this.label ) {
			return '';
		} else {
			return this.label.value;
		}
	}

	public set value( value ) {
		this.editLabel( { language: this.languageCode, value } );
	}

}
</script>

<style lang="scss">
.wb-ui-label-edit {
	@include labelFont( #{&}--primary );
	@include termInput();
	@include termInputStandaloneField();
}
</style>
