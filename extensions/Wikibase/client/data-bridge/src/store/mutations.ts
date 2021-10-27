import { DataValue } from '@wmde/wikibase-datamodel-types';
import EditDecision from '@/definitions/EditDecision';
import clone from '@/store/clone';
import { ValidApplicationStatus } from '@/definitions/ApplicationStatus';
import Term from '@/datamodel/Term';
import ApplicationError from '@/definitions/ApplicationError';
import { BaseState } from './BaseState';
import { Mutations } from 'vuex-smart-module';
import Application from '@/store/Application';

export class RootMutations extends Mutations<Application> {

	public setPropertyPointer( targetProperty: string ): void {
		this.state.targetProperty = targetProperty;
	}

	public setEditFlow( editFlow: string ): void {
		this.state.editFlow = editFlow;
	}

	public setApplicationStatus( status: ValidApplicationStatus ): void {
		this.state.applicationStatus = status;
	}

	public setTargetLabel( label: Term ): void {
		this.state.targetLabel = label;
	}

	public setRenderedTargetReferences( referencesHtml: readonly string[] ): void {
		this.state.renderedTargetReferences = referencesHtml;
	}

	public addApplicationErrors( errors: readonly ApplicationError[] ): void {
		this.state.applicationErrors.push( ...errors );
	}

	public clearApplicationErrors(): void {
		this.state.applicationErrors = [];
	}

	public setEditDecision( editDecision: EditDecision ): void {
		this.state.editDecision = editDecision;
	}

	public setTargetValue( dataValue: DataValue ): void {
		this.state.targetValue = clone( dataValue );
	}

	public setEntityTitle( entityTitle: string ): void {
		this.state.entityTitle = entityTitle;
	}

	public setPageTitle( pageTitle: string ): void {
		this.state.pageTitle = pageTitle;
	}

	public setOriginalHref( orginalHref: string ): void {
		this.state.originalHref = orginalHref;
	}

	public setPageUrl( pageUrl: string ): void {
		this.state.pageUrl = pageUrl;
	}

	public setShowWarningAnonymousEdit( showWarningAnonymousEdit: boolean ): void {
		this.state.showWarningAnonymousEdit = showWarningAnonymousEdit;
	}

	public setAssertUserWhenSaving( assertUserWhenSaving: boolean ): void {
		this.state.assertUserWhenSaving = assertUserWhenSaving;
	}

	public reset(): void {
		// this.state already has all the properties, and Object.assign() invokes setters, so this is reactive
		Object.assign( this.state, new BaseState() );
	}
}
