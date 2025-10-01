<?php declare( strict_types=1 );

namespace Wikibase\Repo\Tests\RestApi\Application\UseCaseRequestValidation;

use LogicException;
use MediaWiki\MediaWikiServices;
use Psr\Container\ContainerInterface;
use Wikibase\Lib\Store\HashSiteLinkStore;
use Wikibase\Repo\RestApi\Application\Serialization\ReferenceDeserializer;
use Wikibase\Repo\RestApi\Application\Serialization\SitelinkDeserializer;
use Wikibase\Repo\RestApi\Application\Serialization\StatementDeserializer;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\EditMetadataRequestValidatingDeserializer;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\SiteIdRequestValidatingDeserializer;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\SitelinkEditRequestValidatingDeserializer;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\StatementSerializationRequestValidatingDeserializer;
use Wikibase\Repo\RestApi\Application\Validation\EditMetadataValidator;
use Wikibase\Repo\RestApi\Application\Validation\SiteIdValidator;
use Wikibase\Repo\RestApi\Application\Validation\StatementValidator;
use Wikibase\Repo\RestApi\Infrastructure\SiteLinkLookupSitelinkValidator;
use Wikibase\Repo\RestApi\Infrastructure\ValidatingRequestDeserializer as VRD;
use Wikibase\Repo\Tests\RestApi\Helpers\TestPropertyValuePairDeserializerFactory;
use Wikibase\Repo\Tests\RestApi\Infrastructure\DataAccess\DummyItemRevisionMetaDataRetriever;
use Wikibase\Repo\Tests\RestApi\Infrastructure\DataAccess\SameTitleSitelinkTargetResolver;

/**
 * @license GPL-2.0-or-later
 */
class TestValidatingRequestDeserializerServiceContainer implements ContainerInterface {

	/**
	 * Returns the real implementation for most validators, and test doubles for some that require certain database data, e.g. depend on a
	 * property data type lookup.
	 * @inheritDoc
	 */
	public function get( string $id ) {
		switch ( $id ) {
			case VRD::EDIT_METADATA_REQUEST_VALIDATING_DESERIALIZER:
				return new EditMetadataRequestValidatingDeserializer(
					new EditMetadataValidator( 500, TestValidatingRequestDeserializer::ALLOWED_TAGS )
				);
			case VRD::SITE_ID_REQUEST_VALIDATING_DESERIALIZER:
				return new SiteIdRequestValidatingDeserializer(
					new SiteIdValidator( TestValidatingRequestDeserializer::ALLOWED_SITE_IDS )
				);
			case VRD::SITELINK_EDIT_REQUEST_VALIDATING_DESERIALIZER:
				return new SitelinkEditRequestValidatingDeserializer(
					new SiteLinkLookupSitelinkValidator(
						new SitelinkDeserializer(
							TestValidatingRequestDeserializer::INVALID_TITLE_REGEX,
							TestValidatingRequestDeserializer::ALLOWED_BADGES,
							new SameTitleSitelinkTargetResolver(),
							new DummyItemRevisionMetaDataRetriever()
						),
						new HashSiteLinkStore()
					)
				);
			case VRD::STATEMENT_SERIALIZATION_REQUEST_VALIDATING_DESERIALIZER:
				$deserializerFactory = new TestPropertyValuePairDeserializerFactory();
				$deserializerFactory->setDataTypeForProperty( TestValidatingRequestDeserializer::EXISTING_STRING_PROPERTY, 'string' );
				$propertyValuePairDeserializer = $deserializerFactory->createPropertyValuePairDeserializer();

				return new StatementSerializationRequestValidatingDeserializer(
					new StatementValidator(
						new StatementDeserializer(
							$propertyValuePairDeserializer,
							new ReferenceDeserializer( $propertyValuePairDeserializer )
						)
					)
				);
		}
		return MediaWikiServices::getInstance()->get( $id );
	}

	/**
	 * @inheritDoc
	 */
	public function has( string $id ): bool {
		throw new LogicException( 'This is not expected to be called.' );
	}
}
