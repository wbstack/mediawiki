<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi;

use MediaWiki\MediaWikiServices;
use Psr\Container\ContainerInterface;
use Wikibase\Repo\RestApi\Application\Serialization\SitelinkDeserializer;
use Wikibase\Repo\RestApi\Application\Serialization\StatementDeserializer;
use Wikibase\Repo\RestApi\Application\Serialization\StatementSerializer;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\EditMetadataRequestValidatingDeserializer;
use Wikibase\Repo\RestApi\Application\UseCases\AddItemAliasesInLanguage\AddItemAliasesInLanguage;
use Wikibase\Repo\RestApi\Application\UseCases\AddItemStatement\AddItemStatement;
use Wikibase\Repo\RestApi\Application\UseCases\AddPropertyAliasesInLanguage\AddPropertyAliasesInLanguage;
use Wikibase\Repo\RestApi\Application\UseCases\AddPropertyStatement\AddPropertyStatement;
use Wikibase\Repo\RestApi\Application\UseCases\AssertItemExists;
use Wikibase\Repo\RestApi\Application\UseCases\AssertPropertyExists;
use Wikibase\Repo\RestApi\Application\UseCases\AssertStatementSubjectExists;
use Wikibase\Repo\RestApi\Application\UseCases\AssertUserIsAuthorized;
use Wikibase\Repo\RestApi\Application\UseCases\CreateItem\CreateItem;
use Wikibase\Repo\RestApi\Application\UseCases\CreateProperty\CreateProperty;
use Wikibase\Repo\RestApi\Application\UseCases\GetItem\GetItem;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemAliases\GetItemAliases;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemAliasesInLanguage\GetItemAliasesInLanguage;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemDescription\GetItemDescription;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemDescriptions\GetItemDescriptions;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemDescriptionWithFallback\GetItemDescriptionWithFallback;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemLabel\GetItemLabel;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemLabels\GetItemLabels;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemLabelWithFallback\GetItemLabelWithFallback;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemStatement\GetItemStatement;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemStatements\GetItemStatements;
use Wikibase\Repo\RestApi\Application\UseCases\GetLatestItemRevisionMetadata;
use Wikibase\Repo\RestApi\Application\UseCases\GetLatestPropertyRevisionMetadata;
use Wikibase\Repo\RestApi\Application\UseCases\GetLatestStatementSubjectRevisionMetadata;
use Wikibase\Repo\RestApi\Application\UseCases\GetProperty\GetProperty;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyAliases\GetPropertyAliases;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyAliasesInLanguage\GetPropertyAliasesInLanguage;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyDescription\GetPropertyDescription;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyDescriptions\GetPropertyDescriptions;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyDescriptionWithFallback\GetPropertyDescriptionWithFallback;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyLabel\GetPropertyLabel;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyLabels\GetPropertyLabels;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyLabelWithFallback\GetPropertyLabelWithFallback;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyStatement\GetPropertyStatement;
use Wikibase\Repo\RestApi\Application\UseCases\GetPropertyStatements\GetPropertyStatements;
use Wikibase\Repo\RestApi\Application\UseCases\GetSitelink\GetSitelink;
use Wikibase\Repo\RestApi\Application\UseCases\GetSitelinks\GetSitelinks;
use Wikibase\Repo\RestApi\Application\UseCases\GetStatement\GetStatement;
use Wikibase\Repo\RestApi\Application\UseCases\PatchItem\PatchItem;
use Wikibase\Repo\RestApi\Application\UseCases\PatchItemAliases\PatchItemAliases;
use Wikibase\Repo\RestApi\Application\UseCases\PatchItemDescriptions\PatchItemDescriptions;
use Wikibase\Repo\RestApi\Application\UseCases\PatchItemLabels\PatchItemLabels;
use Wikibase\Repo\RestApi\Application\UseCases\PatchItemStatement\PatchItemStatement;
use Wikibase\Repo\RestApi\Application\UseCases\PatchProperty\PatchProperty;
use Wikibase\Repo\RestApi\Application\UseCases\PatchPropertyAliases\PatchPropertyAliases;
use Wikibase\Repo\RestApi\Application\UseCases\PatchPropertyDescriptions\PatchPropertyDescriptions;
use Wikibase\Repo\RestApi\Application\UseCases\PatchPropertyLabels\PatchPropertyLabels;
use Wikibase\Repo\RestApi\Application\UseCases\PatchPropertyStatement\PatchPropertyStatement;
use Wikibase\Repo\RestApi\Application\UseCases\PatchSitelinks\PatchSitelinks;
use Wikibase\Repo\RestApi\Application\UseCases\PatchStatement\PatchStatement;
use Wikibase\Repo\RestApi\Application\UseCases\RemoveItemDescription\RemoveItemDescription;
use Wikibase\Repo\RestApi\Application\UseCases\RemoveItemLabel\RemoveItemLabel;
use Wikibase\Repo\RestApi\Application\UseCases\RemoveItemStatement\RemoveItemStatement;
use Wikibase\Repo\RestApi\Application\UseCases\RemovePropertyDescription\RemovePropertyDescription;
use Wikibase\Repo\RestApi\Application\UseCases\RemovePropertyLabel\RemovePropertyLabel;
use Wikibase\Repo\RestApi\Application\UseCases\RemovePropertyStatement\RemovePropertyStatement;
use Wikibase\Repo\RestApi\Application\UseCases\RemoveSitelink\RemoveSitelink;
use Wikibase\Repo\RestApi\Application\UseCases\RemoveStatement\RemoveStatement;
use Wikibase\Repo\RestApi\Application\UseCases\ReplaceItemStatement\ReplaceItemStatement;
use Wikibase\Repo\RestApi\Application\UseCases\ReplacePropertyStatement\ReplacePropertyStatement;
use Wikibase\Repo\RestApi\Application\UseCases\ReplaceStatement\ReplaceStatement;
use Wikibase\Repo\RestApi\Application\UseCases\SetItemDescription\SetItemDescription;
use Wikibase\Repo\RestApi\Application\UseCases\SetItemLabel\SetItemLabel;
use Wikibase\Repo\RestApi\Application\UseCases\SetPropertyDescription\SetPropertyDescription;
use Wikibase\Repo\RestApi\Application\UseCases\SetPropertyLabel\SetPropertyLabel;
use Wikibase\Repo\RestApi\Application\UseCases\SetSitelink\SetSitelink;
use Wikibase\Repo\RestApi\Application\Validation\AliasLanguageCodeValidator;
use Wikibase\Repo\RestApi\Application\Validation\DescriptionLanguageCodeValidator;
use Wikibase\Repo\RestApi\Application\Validation\LabelLanguageCodeValidator;
use Wikibase\Repo\RestApi\Domain\Services\StatementRemover;
use Wikibase\Repo\RestApi\Domain\Services\StatementUpdater;
use Wikibase\Repo\RestApi\Infrastructure\DataAccess\EntityRevisionLookupItemDataRetriever;
use Wikibase\Repo\RestApi\Infrastructure\DataAccess\EntityRevisionLookupPropertyDataRetriever;
use Wikibase\Repo\RestApi\Infrastructure\DataAccess\EntityRevisionLookupStatementRetriever;
use Wikibase\Repo\RestApi\Infrastructure\DataAccess\EntityUpdater;
use Wikibase\Repo\RestApi\Infrastructure\DataAccess\EntityUpdaterItemUpdater;
use Wikibase\Repo\RestApi\Infrastructure\DataAccess\EntityUpdaterPropertyUpdater;
use Wikibase\Repo\RestApi\Infrastructure\DataAccess\TermLookupEntityTermsRetriever;
use Wikibase\Repo\RestApi\Infrastructure\ValidatingRequestDeserializer;
use Wikibase\Repo\RestApi\RouteHandlers\Middleware\PreconditionMiddlewareFactory;
use Wikibase\Repo\RestApi\RouteHandlers\Middleware\StatementRedirectMiddlewareFactory;
use Wikibase\Repo\RestApi\RouteHandlers\Middleware\UnexpectedErrorHandlerMiddleware;

/**
 * @license GPL-2.0-or-later
 */
class WbRestApi {

	public static function getGetItem( ?ContainerInterface $services = null ): GetItem {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetItem' );
	}

	public static function getCreateItem( ?ContainerInterface $services = null ): CreateItem {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.CreateItem' );
	}

	public static function getGetSitelinks( ?ContainerInterface $services = null ): GetSitelinks {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetSitelinks' );
	}

	public static function getGetSitelink( ?ContainerInterface $services = null ): GetSitelink {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetSitelink' );
	}

	public static function getGetItemLabels( ?ContainerInterface $services = null ): GetItemLabels {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetItemLabels' );
	}

	public static function getGetItemLabel( ?ContainerInterface $services = null ): GetItemLabel {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetItemLabel' );
	}

	public static function getGetItemLabelWithFallback( ?ContainerInterface $services = null ): GetItemLabelWithFallback {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetItemLabelWithFallback' );
	}

	public static function getGetItemDescriptions( ?ContainerInterface $services = null ): GetItemDescriptions {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetItemDescriptions' );
	}

	public static function getGetItemDescription( ?ContainerInterface $services = null ): GetItemDescription {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetItemDescription' );
	}

	public static function getGetItemDescriptionWithFallback( ?ContainerInterface $services = null ): GetItemDescriptionWithFallback {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetItemDescriptionWithFallback' );
	}

	public static function getGetItemAliases( ?ContainerInterface $services = null ): GetItemAliases {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetItemAliases' );
	}

	public static function getGetItemAliasesInLanguage( ?ContainerInterface $services = null ): GetItemAliasesInLanguage {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetItemAliasesInLanguage' );
	}

	public static function getSetItemLabel( ?ContainerInterface $services = null ): SetItemLabel {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.SetItemLabel' );
	}

	public static function getSetPropertyLabel( ?ContainerInterface $services = null ): SetPropertyLabel {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.SetPropertyLabel' );
	}

	public static function getSetItemDescription( ?ContainerInterface $services = null ): SetItemDescription {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.SetItemDescription' );
	}

	public static function getSetPropertyDescription( ?ContainerInterface $services = null ): SetPropertyDescription {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.SetPropertyDescription' );
	}

	public static function getGetItemStatements( ?ContainerInterface $services = null ): GetItemStatements {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetItemStatements' );
	}

	public static function getGetItemStatement( ?ContainerInterface $services = null ): GetItemStatement {
		return ( $services ?: MediaWikiServices::getInstance() )->get( 'WbRestApi.GetItemStatement' );
	}

	public static function getGetStatement( ?ContainerInterface $services = null ): GetStatement {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetStatement' );
	}

	public static function getAddItemStatement( ?ContainerInterface $services = null ): AddItemStatement {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.AddItemStatement' );
	}

	public static function getAddPropertyStatement( ?ContainerInterface $services = null ): AddPropertyStatement {
		return ( $services ?: MediaWikiServices::getInstance() )->get( 'WbRestApi.AddPropertyStatement' );
	}

	public static function getReplaceItemStatement( ?ContainerInterface $services = null ): ReplaceItemStatement {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.ReplaceItemStatement' );
	}

	public static function getReplacePropertyStatement( ?ContainerInterface $services = null ): ReplacePropertyStatement {
		return ( $services ?: MediaWikiServices::getInstance() )->get( 'WbRestApi.ReplacePropertyStatement' );
	}

	public static function getReplaceStatement( ?ContainerInterface $services = null ): ReplaceStatement {
		return ( $services ?: MediaWikiServices::getInstance() )->get( 'WbRestApi.ReplaceStatement' );
	}

	public static function getRemoveItemLabel( ?ContainerInterface $services = null ): RemoveItemLabel {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.RemoveItemLabel' );
	}

	public static function getRemovePropertyLabel( ?ContainerInterface $services = null ): RemovePropertyLabel {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.RemovePropertyLabel' );
	}

	public static function getRemoveItemDescription( ?ContainerInterface $services = null ): RemoveItemDescription {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.RemoveItemDescription' );
	}

	public static function getRemovePropertyDescription( ?ContainerInterface $services = null ): RemovePropertyDescription {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.RemovePropertyDescription' );
	}

	public static function getRemoveItemStatement( ?ContainerInterface $services = null ): RemoveItemStatement {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.RemoveItemStatement' );
	}

	public static function getRemovePropertyStatement( ?ContainerInterface $services = null ): RemovePropertyStatement {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.RemovePropertyStatement' );
	}

	public static function getRemoveStatement( ?ContainerInterface $services = null ): RemoveStatement {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.RemoveStatement' );
	}

	public static function getPreconditionMiddlewareFactory( ?ContainerInterface $services = null ): PreconditionMiddlewareFactory {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PreconditionMiddlewareFactory' );
	}

	public static function getPatchStatement( ?ContainerInterface $services = null ): PatchStatement {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PatchStatement' );
	}

	public static function getPatchItemStatement( ?ContainerInterface $services = null ): PatchItemStatement {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PatchItemStatement' );
	}

	public static function getPatchPropertyStatement( ?ContainerInterface $services = null ): PatchPropertyStatement {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PatchPropertyStatement' );
	}

	public static function getPatchPropertyLabels( ?ContainerInterface $services = null ): PatchPropertyLabels {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PatchPropertyLabels' );
	}

	public static function getPatchPropertyDescriptions( ?ContainerInterface $services = null ): PatchPropertyDescriptions {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PatchPropertyDescriptions' );
	}

	public static function getEntityUpdater( ?ContainerInterface $services = null ): EntityUpdater {
		return ( $services ?: MediaWikiServices::getInstance() )->get( 'WbRestApi.EntityUpdater' );
	}

	public static function getItemUpdater( ?ContainerInterface $services = null ): EntityUpdaterItemUpdater {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.ItemUpdater' );
	}

	public static function getPropertyUpdater( ?ContainerInterface $services = null ): EntityUpdaterPropertyUpdater {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PropertyUpdater' );
	}

	public static function getStatementUpdater( ?ContainerInterface $services = null ): StatementUpdater {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.StatementUpdater' );
	}

	public static function getStatementRemover( ?ContainerInterface $services = null ): StatementRemover {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.StatementRemover' );
	}

	public static function getItemDataRetriever( ?ContainerInterface $services = null ): EntityRevisionLookupItemDataRetriever {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.ItemDataRetriever' );
	}

	public static function getSitelinkDeserializer( ?ContainerInterface $services = null ): SitelinkDeserializer {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.SitelinkDeserializer' );
	}

	public static function getStatementRetriever( ?ContainerInterface $services = null ): EntityRevisionLookupStatementRetriever {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.StatementRetriever' );
	}

	public static function getStatementSerializer( ?ContainerInterface $services = null ): StatementSerializer {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.StatementSerializer' );
	}

	public static function getStatementDeserializer( ?ContainerInterface $services = null ): StatementDeserializer {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.StatementDeserializer' );
	}

	public static function getStatementRedirectMiddlewareFactory(
		?ContainerInterface $services = null
	): StatementRedirectMiddlewareFactory {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.StatementRedirectMiddlewareFactory' );
	}

	public static function getUnexpectedErrorHandlerMiddleware( ?ContainerInterface $services = null ): UnexpectedErrorHandlerMiddleware {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.UnexpectedErrorHandlerMiddleware' );
	}

	public static function getPatchItem( ?ContainerInterface $services = null ): PatchItem {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PatchItem' );
	}

	public static function getPatchItemLabels( ?ContainerInterface $services = null ): PatchItemLabels {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PatchItemLabels' );
	}

	public static function getPatchItemDescriptions( ?ContainerInterface $services = null ): PatchItemDescriptions {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PatchItemDescriptions' );
	}

	public static function getPatchItemAliases( ?ContainerInterface $services = null ): PatchItemAliases {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PatchItemAliases' );
	}

	public static function getPatchProperty( ?ContainerInterface $services = null ): PatchProperty {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PatchProperty' );
	}

	public static function getPatchPropertyAliases( ?ContainerInterface $services = null ): PatchPropertyAliases {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PatchPropertyAliases' );
	}

	public static function getPatchSitelinks( ?ContainerInterface $services = null ): PatchSitelinks {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PatchSitelinks' );
	}

	public static function getAssertUserIsAuthorized( ?ContainerInterface $services = null ): AssertUserIsAuthorized {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.AssertUserIsAuthorized' );
	}

	public static function getGetLatestItemRevisionMetadata( ?ContainerInterface $services = null ): GetLatestItemRevisionMetadata {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetLatestItemRevisionMetadata' );
	}

	public static function getAssertItemExists( ?ContainerInterface $services = null ): AssertItemExists {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.AssertItemExists' );
	}

	public static function getAssertPropertyExists( ?ContainerInterface $services = null ): AssertPropertyExists {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.AssertPropertyExists' );
	}

	public static function getAssertStatementSubjectExists( ?ContainerInterface $services = null ): AssertStatementSubjectExists {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.AssertStatementSubjectExists' );
	}

	public static function getGetProperty( ?ContainerInterface $services = null ): GetProperty {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetProperty' );
	}

	public static function getCreateProperty( ?ContainerInterface $services = null ): CreateProperty {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.CreateProperty' );
	}

	public static function getPropertyDataRetriever( ?ContainerInterface $services = null ): EntityRevisionLookupPropertyDataRetriever {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.PropertyDataRetriever' );
	}

	public static function getGetLatestPropertyRevisionMetadata( ?ContainerInterface $services = null ): GetLatestPropertyRevisionMetadata {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetLatestPropertyRevisionMetadata' );
	}

	public static function getGetLatestStatementSubjectRevisionMetadata(
		?ContainerInterface $services = null
	): GetLatestStatementSubjectRevisionMetadata {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetLatestStatementSubjectRevisionMetadata' );
	}

	public static function getGetPropertyStatement( ?ContainerInterface $services = null ): GetPropertyStatement {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetPropertyStatement' );
	}

	public static function getGetPropertyStatements( ?ContainerInterface $services = null ): GetPropertyStatements {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetPropertyStatements' );
	}

	public static function getGetPropertyLabel( ?ContainerInterface $services = null ): GetPropertyLabel {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetPropertyLabel' );
	}

	public static function getGetPropertyLabelWithFallback( ?ContainerInterface $services = null ): GetPropertyLabelWithFallback {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetPropertyLabelWithFallback' );
	}

	public static function getGetPropertyLabels( ?ContainerInterface $services = null ): GetPropertyLabels {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetPropertyLabels' );
	}

	public static function getGetPropertyDescription( ?ContainerInterface $services = null ): GetPropertyDescription {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetPropertyDescription' );
	}

	public static function getGetPropertyDescriptions( ?ContainerInterface $services = null ): GetPropertyDescriptions {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetPropertyDescriptions' );
	}

	public static function getGetPropertyDescriptionWithFallback(
		?ContainerInterface $services = null
	): GetPropertyDescriptionWithFallback {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetPropertyDescriptionWithFallback' );
	}

	public static function getGetPropertyAliasesInLanguage( ?ContainerInterface $services = null ): GetPropertyAliasesInLanguage {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetPropertyAliasesInLanguage' );
	}

	public static function getGetPropertyAliases( ?ContainerInterface $services = null ): GetPropertyAliases {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.GetPropertyAliases' );
	}

	public static function getValidatingRequestDeserializer( ?ContainerInterface $services = null ): ValidatingRequestDeserializer {
		return ( $services ?: MediaWikiServices::getInstance() )->get( 'WbRestApi.ValidatingRequestDeserializer' );
	}

	public static function getTermLookupEntityTermsRetriever( ?ContainerInterface $services = null ): TermLookupEntityTermsRetriever {
		return ( $services ?: MediaWikiServices::getInstance() )->get( 'WbRestApi.TermLookupEntityTermsRetriever' );
	}

	public static function getAddItemAliasesInLanguage( ?ContainerInterface $services = null ): AddItemAliasesInLanguage {
		return ( $services ?: MediaWikiServices::getInstance() )->get( 'WbRestApi.AddItemAliasesInLanguage' );
	}

	public static function getAddPropertyAliasesInLanguage( ?ContainerInterface $services = null ): AddPropertyAliasesInLanguage {
		return ( $services ?: MediaWikiServices::getInstance() )->get( 'WbRestApi.AddPropertyAliasesInLanguage' );
	}

	public static function getRemoveSitelink( ?ContainerInterface $services = null ): RemoveSitelink {
		return ( $services ?: MediaWikiServices::getInstance() )->get( 'WbRestApi.RemoveSitelink' );
	}

	public static function getSetSitelink( ?ContainerInterface $services = null ): SetSitelink {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.SetSitelink' );
	}

	public static function getLabelLanguageCodeValidator( ?ContainerInterface $services = null ): LabelLanguageCodeValidator {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.LabelLanguageCodeValidator' );
	}

	public static function getDescriptionLanguageCodeValidator( ?ContainerInterface $services = null ): DescriptionLanguageCodeValidator {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.DescriptionLanguageCodeValidator' );
	}

	public static function getAliasLanguageCodeValidator( ?ContainerInterface $services = null ): AliasLanguageCodeValidator {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( 'WbRestApi.AliasLanguageCodeValidator' );
	}

	public static function getEditMetadataRequestValidatingDeserializer(
		?ContainerInterface $services = null
	): EditMetadataRequestValidatingDeserializer {
		return ( $services ?: MediaWikiServices::getInstance() )
			->get( ValidatingRequestDeserializer::EDIT_METADATA_REQUEST_VALIDATING_DESERIALIZER );
	}

}
