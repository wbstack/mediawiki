<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Application\UseCaseRequestValidation;

use LogicException;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Term\Term;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;
use Wikibase\Repo\RestApi\Application\Validation\PropertyDescriptionValidator;
use Wikibase\Repo\RestApi\Domain\Services\PropertyWriteModelRetriever;

/**
 * @license GPL-2.0-or-later
 */
class PropertyDescriptionEditRequestValidatingDeserializer {

	private PropertyDescriptionValidator $validator;
	private PropertyWriteModelRetriever $propertyRetriever;

	public function __construct( PropertyDescriptionValidator $validator, PropertyWriteModelRetriever $propertyRetriever ) {
		$this->validator = $validator;
		$this->propertyRetriever = $propertyRetriever;
	}

	/**
	 * @throws UseCaseError
	 */
	public function validateAndDeserialize( PropertyDescriptionEditRequest $request ): Term {
		$property = $this->propertyRetriever->getPropertyWriteModel( new NumericPropertyId( $request->getPropertyId() ) );
		$language = $request->getLanguageCode();
		$description = $request->getDescription();

		// skip if property does not exist or description is unchanged
		if ( !$property ||
			 ( $property->getDescriptions()->hasTermForLanguage( $language ) &&
			   $property->getDescriptions()->getByLanguage( $language )->getText() === $description
			 )
		) {
			return new Term( $language, $description );
		}

		$validationError = $this->validator->validate(
			$language,
			$request->getDescription(),
			$property->getLabels()
		);

		if ( $validationError ) {
			$errorCode = $validationError->getCode();
			$context = $validationError->getContext();
			switch ( $errorCode ) {
				case PropertyDescriptionValidator::CODE_INVALID:
				case PropertyDescriptionValidator::CODE_EMPTY:
					throw UseCaseError::newInvalidValue( '/description' );
				case PropertyDescriptionValidator::CODE_TOO_LONG:
					throw UseCaseError::newValueTooLong( '/description', $context[PropertyDescriptionValidator::CONTEXT_LIMIT] );
				case PropertyDescriptionValidator::CODE_LABEL_DESCRIPTION_EQUAL:
					throw UseCaseError::newDataPolicyViolation(
						UseCaseError::POLICY_VIOLATION_LABEL_DESCRIPTION_SAME_VALUE,
						[ UseCaseError::CONTEXT_LANGUAGE => $context[PropertyDescriptionValidator::CONTEXT_LANGUAGE] ]
					);
				default:
					throw new LogicException( "Unexpected validation error code: $errorCode" );
			}
		}

		return new Term( $language, $request->getDescription() );
	}

}
