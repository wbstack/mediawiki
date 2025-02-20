<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Infrastructure;

use Psr\Container\ContainerInterface;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\AliasLanguageCodeRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\DescriptionLanguageCodeRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\DeserializedRequestAdapter;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\EditMetadataRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\ItemAliasesInLanguageEditRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\ItemDescriptionEditRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\ItemFieldsRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\ItemIdRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\ItemLabelEditRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\ItemSerializationRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\ItemStatementIdRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\LabelLanguageCodeRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\PatchRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\PropertyAliasesInLanguageEditRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\PropertyDescriptionEditRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\PropertyFieldsRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\PropertyIdFilterRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\PropertyIdRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\PropertyLabelEditRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\PropertyStatementIdRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\SiteIdRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\SitelinkEditRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\StatementIdRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\StatementSerializationRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\UseCaseRequest;
use Wikibase\Repo\RestApi\Application\UseCases\AddItemAliasesInLanguage\AddItemAliasesInLanguageValidator;
use Wikibase\Repo\RestApi\Application\UseCases\AddItemStatement\AddItemStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\AddPropertyAliasesInLanguage\AddPropertyAliasesInLanguageValidator;
use Wikibase\Repo\RestApi\Application\UseCases\AddPropertyStatement\AddPropertyStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\CreateItem\CreateItemValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetItem\GetItemValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemAliases\GetItemAliasesValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemAliasesInLanguage\GetItemAliasesInLanguageValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemDescription\GetItemDescriptionValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemDescriptions\GetItemDescriptionsValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemDescriptionWithFallback\GetItemDescriptionWithFallbackValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemLabel\GetItemLabelValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemLabels\GetItemLabelsValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemLabelWithFallback\GetItemLabelWithFallbackValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemStatement\GetItemStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemStatements\GetItemStatementsValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetProperty\GetPropertyValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyAliases\GetPropertyAliasesValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyAliasesInLanguage\GetPropertyAliasesInLanguageValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyDescription\GetPropertyDescriptionValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyDescriptions\GetPropertyDescriptionsValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyDescriptionWithFallback\GetPropertyDescriptionWithFallbackValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyLabel\GetPropertyLabelValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyLabels\GetPropertyLabelsValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyLabelWithFallback\GetPropertyLabelWithFallbackValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyStatement\GetPropertyStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyStatements\GetPropertyStatementsValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetSitelink\GetSitelinkValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetSitelinks\GetSitelinksValidator;
use Wikibase\Repo\RestApi\Application\UseCases\GetStatement\GetStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\PatchItem\PatchItemValidator;
use Wikibase\Repo\RestApi\Application\UseCases\PatchItemAliases\PatchItemAliasesValidator;
use Wikibase\Repo\RestApi\Application\UseCases\PatchItemDescriptions\PatchItemDescriptionsValidator;
use Wikibase\Repo\RestApi\Application\UseCases\PatchItemLabels\PatchItemLabelsValidator;
use Wikibase\Repo\RestApi\Application\UseCases\PatchItemStatement\PatchItemStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\PatchProperty\PatchPropertyValidator;
use Wikibase\Repo\RestApi\Application\UseCases\PatchPropertyAliases\PatchPropertyAliasesValidator;
use Wikibase\Repo\RestApi\Application\UseCases\PatchPropertyDescriptions\PatchPropertyDescriptionsValidator;
use Wikibase\Repo\RestApi\Application\UseCases\PatchPropertyLabels\PatchPropertyLabelsValidator;
use Wikibase\Repo\RestApi\Application\UseCases\PatchPropertyStatement\PatchPropertyStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\PatchSitelinks\PatchSitelinksValidator;
use Wikibase\Repo\RestApi\Application\UseCases\PatchStatement\PatchStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\RemoveItemDescription\RemoveItemDescriptionValidator;
use Wikibase\Repo\RestApi\Application\UseCases\RemoveItemLabel\RemoveItemLabelValidator;
use Wikibase\Repo\RestApi\Application\UseCases\RemoveItemStatement\RemoveItemStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\RemovePropertyDescription\RemovePropertyDescriptionValidator;
use Wikibase\Repo\RestApi\Application\UseCases\RemovePropertyLabel\RemovePropertyLabelValidator;
use Wikibase\Repo\RestApi\Application\UseCases\RemovePropertyStatement\RemovePropertyStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\RemoveSitelink\RemoveSitelinkValidator;
use Wikibase\Repo\RestApi\Application\UseCases\RemoveStatement\RemoveStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\ReplaceItemStatement\ReplaceItemStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\ReplacePropertyStatement\ReplacePropertyStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\ReplaceStatement\ReplaceStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\SetItemDescription\SetItemDescriptionValidator;
use Wikibase\Repo\RestApi\Application\UseCases\SetItemLabel\SetItemLabelValidator;
use Wikibase\Repo\RestApi\Application\UseCases\SetPropertyDescription\SetPropertyDescriptionValidator;
use Wikibase\Repo\RestApi\Application\UseCases\SetPropertyLabel\SetPropertyLabelValidator;
use Wikibase\Repo\RestApi\Application\UseCases\SetSitelink\SetSitelinkValidator;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;

/**
 * @license GPL-2.0-or-later
 */
class ValidatingRequestDeserializer	implements
	AddItemStatementValidator,
	AddPropertyStatementValidator,
	GetItemValidator,
	GetSitelinksValidator,
	GetSitelinkValidator,
	GetItemLabelsValidator,
	GetItemLabelValidator,
	GetItemLabelWithFallbackValidator,
	GetItemDescriptionsValidator,
	GetItemDescriptionValidator,
	GetItemDescriptionWithFallbackValidator,
	GetItemAliasesValidator,
	GetItemAliasesInLanguageValidator,
	GetItemStatementValidator,
	GetItemStatementsValidator,
	GetPropertyValidator,
	GetPropertyLabelsValidator,
	GetPropertyDescriptionsValidator,
	GetPropertyDescriptionWithFallbackValidator,
	GetPropertyAliasesValidator,
	GetPropertyAliasesInLanguageValidator,
	GetPropertyStatementValidator,
	GetPropertyStatementsValidator,
	GetStatementValidator,
	PatchItemValidator,
	PatchItemLabelsValidator,
	PatchItemDescriptionsValidator,
	PatchItemAliasesValidator,
	PatchItemStatementValidator,
	PatchPropertyValidator,
	PatchPropertyStatementValidator,
	PatchStatementValidator,
	RemoveItemLabelValidator,
	RemoveItemDescriptionValidator,
	RemoveItemStatementValidator,
	RemovePropertyLabelValidator,
	RemovePropertyDescriptionValidator,
	RemovePropertyStatementValidator,
	RemoveStatementValidator,
	ReplaceItemStatementValidator,
	ReplacePropertyStatementValidator,
	ReplaceStatementValidator,
	SetItemLabelValidator,
	SetItemDescriptionValidator,
	GetPropertyLabelValidator,
	GetPropertyDescriptionValidator,
	GetPropertyLabelWithFallbackValidator,
	SetPropertyDescriptionValidator,
	PatchPropertyLabelsValidator,
	PatchPropertyDescriptionsValidator,
	PatchPropertyAliasesValidator,
	SetPropertyLabelValidator,
	AddItemAliasesInLanguageValidator,
	AddPropertyAliasesInLanguageValidator,
	RemoveSitelinkValidator,
	SetSitelinkValidator,
	PatchSitelinksValidator,
	CreateItemValidator
{
	private const PREFIX = 'WbRestApi.RequestValidation.';
	public const ITEM_ID_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'ItemIdRequestValidatingDeserializer';
	public const PROPERTY_ID_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'PropertyIdRequestValidatingDeserializer';
	public const STATEMENT_ID_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'StatementIdRequestValidatingDeserializer';
	public const PROPERTY_ID_FILTER_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'PropertyIdFilterRequestValidatingDeserializer';
	public const LABEL_LANGUAGE_CODE_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'LabelLanguageCodeRequestValidatingDeserializer';
	public const DESCRIPTION_LANGUAGE_CODE_REQUEST_VALIDATING_DESERIALIZER =
		self::PREFIX . 'DescriptionLanguageCodeRequestValidatingDeserializer';
	public const ALIAS_LANGUAGE_CODE_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'AliasLanguageCodeRequestValidatingDeserializer';
	public const SITE_ID_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'SiteIdRequestValidatingDeserializer';
	public const ITEM_FIELDS_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'ItemFieldsRequestValidatingDeserializer';
	public const PROPERTY_FIELDS_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'PropertyFieldsRequestValidatingDeserializer';
	public const STATEMENT_SERIALIZATION_REQUEST_VALIDATING_DESERIALIZER =
		self::PREFIX . 'StatementSerializationRequestValidatingDeserializer';
	public const EDIT_METADATA_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'EditMetadataRequestValidatingDeserializer';
	public const PATCH_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'PatchRequestValidatingDeserializer';
	public const ITEM_LABEL_EDIT_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'ItemLabelEditRequestValidatingDeserializer';
	public const ITEM_DESCRIPTION_EDIT_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'ItemDescriptionEditRequestValidatingDeserializer';
	public const ITEM_ALIASES_IN_LANGUAGE_EDIT_REQUEST_VALIDATING_DESERIALIZER =
		self::PREFIX . 'ItemAliasesEditRequestValidatingDeserializer';

	public const PROPERTY_DESCRIPTION_EDIT_REQUEST_VALIDATING_DESERIALIZER =
		self::PREFIX . 'PropertyDescriptionEditRequestValidatingDeserializer';
	public const PROPERTY_LABEL_EDIT_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'PropertyLabelEditRequestValidatingDeserializer';
	public const PROPERTY_ALIASES_IN_LANGUAGE_EDIT_REQUEST_VALIDATING_DESERIALIZER =
		self::PREFIX . 'PropertyAliasesInLanguageEditRequestValidatingDeserializer';
	public const SITELINK_EDIT_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'SitelinkEditRequestValidatingDeserializer';
	public const ITEM_SERIALIZATION_REQUEST_VALIDATING_DESERIALIZER = self::PREFIX . 'ItemSerializationRequestValidatingDeserializer';
	public const ITEM_STATEMENT_ID_REQUEST_VALIDATOR = self::PREFIX . 'ItemStatementIdRequestValidator';
	public const PROPERTY_STATEMENT_ID_REQUEST_VALIDATOR = self::PREFIX . 'PropertyStatementIdRequestValidator';

	private ContainerInterface $serviceContainer;
	private array $validRequestResults = [];

	/**
	 * @param ContainerInterface $serviceContainer Using the service container here allows us to lazily instantiate only the validators that
	 *   are needed for the request object.
	 */
	public function __construct( ContainerInterface $serviceContainer ) {
		$this->serviceContainer = $serviceContainer;
	}

	/**
	 * @throws UseCaseError
	 */
	public function validateAndDeserialize( UseCaseRequest $request ): DeserializedRequestAdapter {
		$requestObjectId = spl_object_id( $request );
		if ( array_key_exists( $requestObjectId, $this->validRequestResults ) ) {
			return $this->validRequestResults[$requestObjectId];
		}

		$requestTypeToValidatorMap = [
			ItemIdRequest::class => self::ITEM_ID_REQUEST_VALIDATING_DESERIALIZER,
			PropertyIdRequest::class => self::PROPERTY_ID_REQUEST_VALIDATING_DESERIALIZER,
			SiteIdRequest::class => self::SITE_ID_REQUEST_VALIDATING_DESERIALIZER,
			StatementIdRequest::class => self::STATEMENT_ID_REQUEST_VALIDATING_DESERIALIZER,
			PropertyIdFilterRequest::class => self::PROPERTY_ID_FILTER_REQUEST_VALIDATING_DESERIALIZER,
			LabelLanguageCodeRequest::class => self::LABEL_LANGUAGE_CODE_REQUEST_VALIDATING_DESERIALIZER,
			DescriptionLanguageCodeRequest::class => self::DESCRIPTION_LANGUAGE_CODE_REQUEST_VALIDATING_DESERIALIZER,
			AliasLanguageCodeRequest::class => self::ALIAS_LANGUAGE_CODE_REQUEST_VALIDATING_DESERIALIZER,
			ItemFieldsRequest::class => self::ITEM_FIELDS_REQUEST_VALIDATING_DESERIALIZER,
			PropertyFieldsRequest::class => self::PROPERTY_FIELDS_REQUEST_VALIDATING_DESERIALIZER,
			StatementSerializationRequest::class => self::STATEMENT_SERIALIZATION_REQUEST_VALIDATING_DESERIALIZER,
			EditMetadataRequest::class => self::EDIT_METADATA_REQUEST_VALIDATING_DESERIALIZER,
			PatchRequest::class => self::PATCH_REQUEST_VALIDATING_DESERIALIZER,
			ItemLabelEditRequest::class => self::ITEM_LABEL_EDIT_REQUEST_VALIDATING_DESERIALIZER,
			ItemDescriptionEditRequest::class => self::ITEM_DESCRIPTION_EDIT_REQUEST_VALIDATING_DESERIALIZER,
			ItemAliasesInLanguageEditRequest::class => self::ITEM_ALIASES_IN_LANGUAGE_EDIT_REQUEST_VALIDATING_DESERIALIZER,
			PropertyLabelEditRequest::class => self::PROPERTY_LABEL_EDIT_REQUEST_VALIDATING_DESERIALIZER,
			PropertyDescriptionEditRequest::class => self::PROPERTY_DESCRIPTION_EDIT_REQUEST_VALIDATING_DESERIALIZER,
			PropertyAliasesInLanguageEditRequest::class => self::PROPERTY_ALIASES_IN_LANGUAGE_EDIT_REQUEST_VALIDATING_DESERIALIZER,
			SitelinkEditRequest::class => self::SITELINK_EDIT_REQUEST_VALIDATING_DESERIALIZER,
			ItemSerializationRequest::class => self::ITEM_SERIALIZATION_REQUEST_VALIDATING_DESERIALIZER,
			ItemStatementIdRequest::class => self::ITEM_STATEMENT_ID_REQUEST_VALIDATOR,
			PropertyStatementIdRequest::class => self::PROPERTY_STATEMENT_ID_REQUEST_VALIDATOR,
		];
		$result = [];

		foreach ( $requestTypeToValidatorMap as $requestType => $validatorName ) {
			if ( array_key_exists( $requestType, class_implements( $request ) ) ) {
				$result[$requestType] = $this->serviceContainer->get( $validatorName )
					->validateAndDeserialize( $request );
			}
		}

		$this->validRequestResults[$requestObjectId] = new DeserializedRequestAdapter( $result );

		return $this->validRequestResults[$requestObjectId];
	}

}
