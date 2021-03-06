<template>
	<ul
		class="wb-ui-aliases-edit"
		:class="{ 'wb-ui-aliases-edit--focus-within' : hasFocus }"
	>
		<li v-for="( value, index ) in aliasValues" :key="keys[ index ]" class="wb-ui-aliases-edit__item">
			<ResizingTextField
				class="wb-ui-aliases-edit__alias"
				v-inlanguage="languageCode"
				:value="value"
				@input="value => aliasInput( index, value )"
				@focus.native="setFocus()"
				@blur.native="removeAliasIfEmpty( index ); unsetFocus()"
				:placeholder="message( MESSAGE_KEYS.PLACEHOLDER_EDIT_ALIAS )"
				:maxlength="config.textFieldCharacterLimit"
				autocapitalize="off"
			/>
		</li>
	</ul>
</template>

<script lang="ts">
import Component, { mixins } from 'vue-class-component';
import { NS_ENTITY } from '@/store/namespaces';
import Messages from '@/components/mixins/Messages';
import { Prop } from 'vue-property-decorator';
import { namespace } from 'vuex-class';
import Term from '@/datamodel/Term';
import { ResizingTextField } from '@wmde/wikibase-vuejs-components';
import { ENTITY_ALIAS_REMOVE, ENTITY_ALIASES_EDIT } from '@/store/entity/actionTypes';

@Component( {
	components: { ResizingTextField },
} )
export default class AliasesEdit extends mixins( Messages ) {
	@Prop( { required: true } )
	public aliases!: Term[]|null;

	@Prop( { required: true, type: String } )
	public languageCode!: string;

	@namespace( NS_ENTITY ).Action( ENTITY_ALIASES_EDIT )
	public editAliases!: ( payload: { language: string; aliasValues: string[] } ) => void;

	@namespace( NS_ENTITY ).Action( ENTITY_ALIAS_REMOVE )
	public removeAlias!: ( payload: { languageCode: string; index: number } ) => void;

	public hasFocus = false;

	public setFocus(): void {
		this.hasFocus = true;
	}

	public unsetFocus(): void {
		this.hasFocus = false;
	}

	public get aliasValues(): string[] {
		return [ ...( this.aliases || [] ).map( ( alias ) => alias.value ), '' ];
	}

	private keys = [ ... this.aliasValues.keys() ];

	private addAdditionalKey(): void {
		this.keys.push( this.keys[ this.keys.length - 1 ] + 1 );
	}

	public aliasInput( index: number, value: string ): void {
		if ( this.isBottomBlankField( index ) ) {
			this.addNewAlias( value );
		} else {
			this.editAlias( index, value );
		}
	}

	private addNewAlias( value: string ): void {
		if ( value.trim() === '' ) {
			return;
		}

		this.addAdditionalKey();

		this.editAliases( {
			language: this.languageCode,
			aliasValues: this.getValuesWithEdit( this.aliasValues.length - 1, value ),
		} );
	}

	private editAlias( index: number, value: string ): void {
		const aliasValues = this.getValuesWithEdit( index, value );
		aliasValues.splice( aliasValues.length - 1, 1 );

		this.editAliases( {
			language: this.languageCode,
			aliasValues,
		} );
	}

	private getValuesWithEdit( index: number, value: string ): string[] {
		const aliasValues = [ ...this.aliasValues ];
		aliasValues[ index ] = value;

		return aliasValues;
	}

	public removeAliasIfEmpty( index: number ): void {
		if (
			this.aliasValues[ index ].trim() === '' &&
			!this.isBottomBlankField( index )
		) {
			this.removeAlias( { languageCode: this.languageCode, index } );
			this.keys.splice( index, 1 );
		}
	}

	private isBottomBlankField( index: number ): boolean {
		return index === this.aliasValues.length - 1;
	}

}
</script>

<style lang="scss">
.wb-ui-aliases-edit {
	@include termInputStandaloneField();
	background-color: $color-white;
	overflow-y: visible;
	padding: 1px 3px;

	&__item {
		margin-top: 8px;

		&:first-child {
			margin-top: 0;
		}
	}

	&__alias {
		@include aliasesFont();
		@include termInput();
		@include termInputGrouped();

		&:focus {
			color: $alias-edit-focus-color;
		}
	}
}
</style>
