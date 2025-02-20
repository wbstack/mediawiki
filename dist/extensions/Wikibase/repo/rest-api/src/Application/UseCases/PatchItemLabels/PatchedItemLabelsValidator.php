<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Application\UseCases\PatchItemLabels;

use LogicException;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;
use Wikibase\Repo\RestApi\Application\Validation\ItemLabelsContentsValidator;
use Wikibase\Repo\RestApi\Application\Validation\ItemLabelValidator;
use Wikibase\Repo\RestApi\Application\Validation\LabelsSyntaxValidator;
use Wikibase\Repo\RestApi\Application\Validation\LanguageCodeValidator;
use Wikibase\Repo\RestApi\Application\Validation\ValidationError;

/**
 * @license GPL-2.0-or-later
 */
class PatchedItemLabelsValidator {

	private LabelsSyntaxValidator $syntaxValidator;
	private ItemLabelsContentsValidator $contentsValidator;

	public function __construct( LabelsSyntaxValidator $syntaxValidator, ItemLabelsContentsValidator $contentsValidator ) {
		$this->syntaxValidator = $syntaxValidator;
		$this->contentsValidator = $contentsValidator;
	}

	/**
	 * @param TermList $originalLabels
	 * @param TermList $originalDescriptions
	 * @param mixed $labelsSerialization
	 *
	 * @throws UseCaseError
	 * @return TermList
	 */
	public function validateAndDeserialize(
		TermList $originalLabels,
		TermList $originalDescriptions,
		$labelsSerialization
	): TermList {
		if ( !is_array( $labelsSerialization ) ) {
			throw UseCaseError::newPatchResultInvalidValue( '', $labelsSerialization );
		}

		$error = $this->syntaxValidator->validate( $labelsSerialization ) ?:
			$this->contentsValidator->validate(
				$this->syntaxValidator->getPartiallyValidatedLabels(),
				$originalDescriptions,
				$this->getModifiedLanguages( $originalLabels, $this->syntaxValidator->getPartiallyValidatedLabels() )
			);

		if ( $error ) {
			$this->throwUseCaseError( $error );
		}

		return $this->contentsValidator->getValidatedLabels();
	}

	private function getModifiedLanguages( TermList $original, TermList $modified ): array {
		return array_keys( array_filter(
			iterator_to_array( $modified ),
			fn( Term $label ) => !$original->hasTermForLanguage( $label->getLanguageCode() ) ||
				!$original->getByLanguage( $label->getLanguageCode() )->equals( $label )
		) );
	}

	/**
	 * @return never
	 */
	private function throwUseCaseError( ValidationError $validationError ): void {
		$context = $validationError->getContext();
		switch ( $validationError->getCode() ) {
			case LanguageCodeValidator::CODE_INVALID_LANGUAGE_CODE:
				throw UseCaseError::newPatchResultInvalidKey( '', $context[LanguageCodeValidator::CONTEXT_LANGUAGE_CODE] );
			case LabelsSyntaxValidator::CODE_LABELS_NOT_ASSOCIATIVE:
				throw UseCaseError::newPatchResultInvalidValue( '', $context[LabelsSyntaxValidator::CONTEXT_VALUE ] );
			case LabelsSyntaxValidator::CODE_EMPTY_LABEL:
				$languageCode = $context[LabelsSyntaxValidator::CONTEXT_LANGUAGE];
				throw UseCaseError::newPatchResultInvalidValue( "/$languageCode", '' );
			case LabelsSyntaxValidator::CODE_INVALID_LABEL_TYPE:
				$language = $context[LabelsSyntaxValidator::CONTEXT_LANGUAGE];
				$value = $context[LabelsSyntaxValidator::CONTEXT_LABEL];
				throw UseCaseError::newPatchResultInvalidValue( "/$language", $value );
			case ItemLabelValidator::CODE_INVALID:
				$language = $context[ItemLabelValidator::CONTEXT_LANGUAGE];
				$value = $context[ItemLabelValidator::CONTEXT_LABEL];
				throw UseCaseError::newPatchResultInvalidValue( "/$language", $value );
			case ItemLabelValidator::CODE_TOO_LONG:
				$maxLabelLength = $context[ItemLabelValidator::CONTEXT_LIMIT];
				$language = $context[ItemLabelValidator::CONTEXT_LANGUAGE];
				throw UseCaseError::newValueTooLong( "/$language", $maxLabelLength, true );
			case ItemLabelValidator::CODE_LABEL_DESCRIPTION_DUPLICATE:
				throw UseCaseError::newDataPolicyViolation(
					UseCaseError::POLICY_VIOLATION_ITEM_LABEL_DESCRIPTION_DUPLICATE,
					[
						UseCaseError::CONTEXT_LANGUAGE => $context[ItemLabelValidator::CONTEXT_LANGUAGE],
						UseCaseError::CONTEXT_CONFLICTING_ITEM_ID => $context[ItemLabelValidator::CONTEXT_CONFLICTING_ITEM_ID],
					]
				);
			case ItemLabelValidator::CODE_LABEL_SAME_AS_DESCRIPTION:
				throw UseCaseError::newDataPolicyViolation(
					UseCaseError::POLICY_VIOLATION_LABEL_DESCRIPTION_SAME_VALUE,
					[ UseCaseError::CONTEXT_LANGUAGE => $context[ItemLabelValidator::CONTEXT_LANGUAGE] ]
				);
			default:
				throw new LogicException( "Unknown validation error: {$validationError->getCode()}" );
		}
	}
}
