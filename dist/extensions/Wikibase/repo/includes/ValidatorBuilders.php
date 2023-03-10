<?php

namespace Wikibase\Repo;

use DataValues\DataValue;
use DataValues\TimeValue;
use DataValues\UnboundedQuantityValue;
use MediaWiki\Site\MediaWikiPageNameNormalizer;
use ValueValidators\ValueValidator;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Services\Lookup\EntityLookup;
use Wikibase\Lib\ContentLanguages;
use Wikibase\Repo\Validators\AlternativeValidator;
use Wikibase\Repo\Validators\CommonsMediaExistsValidator;
use Wikibase\Repo\Validators\CompositeValidator;
use Wikibase\Repo\Validators\DataFieldValidator;
use Wikibase\Repo\Validators\DataValueValidator;
use Wikibase\Repo\Validators\EntityExistsValidator;
use Wikibase\Repo\Validators\InterWikiLinkExistsValidator;
use Wikibase\Repo\Validators\MembershipValidator;
use Wikibase\Repo\Validators\NumberRangeValidator;
use Wikibase\Repo\Validators\NumberValidator;
use Wikibase\Repo\Validators\RegexValidator;
use Wikibase\Repo\Validators\StringLengthValidator;
use Wikibase\Repo\Validators\TimestampPrecisionValidator;
use Wikibase\Repo\Validators\TypeValidator;
use Wikibase\Repo\Validators\UrlSchemeValidators;
use Wikibase\Repo\Validators\UrlValidator;
use Wikibase\Repo\Validators\WikiLinkExistsValidator;

/**
 * Defines validators for the basic well known data types supported by Wikibase.
 *
 * @warning: This is a low level factory for use by bootstrap code only!
 * Program logic should use an instance of DataTypeValidatorFactory.
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class ValidatorBuilders {

	/**
	 * @var EntityLookup
	 */
	private $entityLookup;

	/**
	 * @var EntityIdParser
	 */
	private $entityIdParser;

	/**
	 * @var string[]
	 */
	private $urlSchemes;

	/**
	 * @var string The base URI for the vocabulary to use for units (and in the
	 * future, globes and calendars).
	 */
	private $itemVocabularyBaseUri;

	/**
	 * @var string The base URI wikibase concepts, for use with the validators for time and globe
	 * values. Our parsers for these data types currently have Wikidata URIs hardcoded, so we need
	 * to hardcode the URI to check them against for now.
	 *
	 * @todo use a configurable vocabulary for calendars and reference globes, instead of
	 * hardcoding wikidata. Then replace usages of $wikidataBaseUri with $vocabularyBaseUri.
	 */
	private $wikidataBaseUri = 'http://www.wikidata.org/entity/';

	/**
	 * @var ContentLanguages
	 */
	private $contentLanguages;

	/**
	 * @var CachingCommonsMediaFileNameLookup
	 */
	private $mediaFileNameLookup;

	/**
	 * @var MediaWikiPageNameNormalizer
	 */
	private $mediaWikiPageNameNormalizer;

	/**
	 * @var string
	 */
	private $geoShapeStorageApiUrl;

	/**
	 * @var string
	 */
	private $tabularDataStorageApiUrl;

	/**
	 * @param EntityLookup $lookup
	 * @param EntityIdParser $idParser
	 * @param string[] $urlSchemes
	 * @param string $itemVocabularyBaseUri The base URI for vocabulary concepts.
	 * @param ContentLanguages $contentLanguages
	 * @param CachingCommonsMediaFileNameLookup $cachingCommonsMediaFileNameLookup
	 * @param MediaWikiPageNameNormalizer $mediaWikiPageNameNormalizer
	 * @param string $geoShapeStorageApiUrl
	 * @param string $tabularDataStorageApiUrl
	 */
	public function __construct(
		EntityLookup $lookup,
		EntityIdParser $idParser,
		array $urlSchemes,
		$itemVocabularyBaseUri,
		ContentLanguages $contentLanguages,
		CachingCommonsMediaFileNameLookup $cachingCommonsMediaFileNameLookup,
		MediaWikiPageNameNormalizer $mediaWikiPageNameNormalizer,
		$geoShapeStorageApiUrl,
		$tabularDataStorageApiUrl
	) {
		$this->entityLookup = $lookup;
		$this->entityIdParser = $idParser;
		$this->urlSchemes = $urlSchemes;
		$this->itemVocabularyBaseUri = $itemVocabularyBaseUri;
		$this->contentLanguages = $contentLanguages;
		$this->mediaFileNameLookup = $cachingCommonsMediaFileNameLookup;
		$this->mediaWikiPageNameNormalizer = $mediaWikiPageNameNormalizer;
		$this->geoShapeStorageApiUrl = $geoShapeStorageApiUrl;
		$this->tabularDataStorageApiUrl = $tabularDataStorageApiUrl;
	}

	/**
	 * @return ValueValidator[]
	 */
	public function buildItemValidators() {
		return $this->getEntityValidators( Item::ENTITY_TYPE );
	}

	/**
	 * @return ValueValidator[]
	 */
	public function buildPropertyValidators() {
		return $this->getEntityValidators( Property::ENTITY_TYPE );
	}

	/**
	 * @return ValueValidator[]
	 */
	public function buildEntityValidators() {
		return $this->getEntityValidators();
	}

	/**
	 * @param string|null $entityType
	 *
	 * @return ValueValidator[]
	 */
	public function getEntityValidators( $entityType = null ) {
		$typeValidator = new TypeValidator( EntityIdValue::class );
		$entityExistsValidator = new EntityExistsValidator( $this->entityLookup, $entityType );

		return [
			$typeValidator,
			$entityExistsValidator,
		];
	}

	/**
	 * @param int $maxLength Defaults to 400 characters. This was an arbitrary decision when it
	 * turned out that 255 was to short for descriptions.
	 *
	 * @return ValueValidator[]
	 */
	private function getCommonStringValidators( $maxLength = 400 ) {
		$validators = [];

		$validators[] = new TypeValidator( 'string' );
		//TODO: validate UTF8 (here and elsewhere)
		$validators[] = new StringLengthValidator( 1, $maxLength, 'mb_strlen' );
		// no leading/trailing whitespace, no tab or vertical whitespace, no line breaks.
		$validators[] = new RegexValidator( '/^\s|[\v\t]|\s$/u', true );

		return $validators;
	}

	/**
	 * @param string $checkExistence Either 'checkExistence' or 'doNotCheckExistence'
	 *
	 * @return ValueValidator[]
	 */
	public function buildMediaValidators( $checkExistence = 'checkExistence' ) {
		// oi_archive_name is max 255 bytes, which include a timestamp and an exclamation mark,
		// so restrict file name to 240 bytes (see UploadBase::getTitle).
		$validators = $this->getCommonStringValidators( 240 );

		//The filename must contain an extension
		$validators[] = new RegexValidator(
			'/.*\.\w{2,}$/u',
			false,
			'check-file-type'
		);

		// Must contain a non-empty file name with no nasty characters (see documentation of
		// $wgLegalTitleChars as well as $wgIllegalFileChars)
		$validators[] = new RegexValidator(
			'/^[^#\/:[\\\\\]{|}]+$/u',
			false,
			'illegal-file-chars'
		);
		if ( $checkExistence === 'checkExistence' ) {
			$validators[] = new CommonsMediaExistsValidator( $this->mediaFileNameLookup );
		}

		$topValidator = new DataValueValidator(
			new CompositeValidator( $validators ) //Note: each validator is fatal
		);

		return [ new TypeValidator( DataValue::class ), $topValidator ];
	}

	/**
	 * @param string $checkExistence Either 'checkExistence' or 'doNotCheckExistence'
	 *
	 * @return ValueValidator[]
	 */
	public function buildGeoShapeValidators( $checkExistence = 'checkExistence' ) {
		$validators = $this->getCommonStringValidators( 240 );
		//Don't forget to change message `wikibase-validator-illegal-geo-shape-title` modifying this
		// Check for 'Data:' prefix, '.map' extension and illegal characters
		$validators[] = new RegexValidator(
			'/^Data:[^\\[\\]#\\\:{|}]+\.map$/u',
			false,
			'illegal-geo-shape-title'
		);
		if ( $checkExistence === 'checkExistence' ) {
			$validators[] = new InterWikiLinkExistsValidator(
				$this->mediaWikiPageNameNormalizer,
				$this->geoShapeStorageApiUrl
			);
		}

		$topValidator = new DataValueValidator(
			new CompositeValidator( $validators ) //Note: each validator is fatal
		);

		return [ new TypeValidator( DataValue::class ), $topValidator ];
	}

	/**
	 * @param string $checkExistence Either 'checkExistence' or 'doNotCheckExistence'
	 *
	 * @return ValueValidator[]
	 */
	public function buildTabularDataValidators( $checkExistence = 'checkExistence' ) {
		$validators = $this->getCommonStringValidators( 240 );
		$validators[] = new RegexValidator(
			'/^Data:[^\\[\\]#\\\:{|}]+\.tab$/u',
			false,
			'illegal-tabular-data-title'
		);
		if ( $checkExistence === 'checkExistence' ) {
			$validators[] = new InterWikiLinkExistsValidator(
				$this->mediaWikiPageNameNormalizer,
				$this->tabularDataStorageApiUrl
			);
		}

		$topValidator = new DataValueValidator(
			new CompositeValidator( $validators )
		);

		return [ new TypeValidator( DataValue::class ), $topValidator ];
	}

	/**
	 * @return ValueValidator[]
	 */
	public function buildEntitySchemaValidators() {
		$validators = [];
		$validators[] = new RegexValidator(
			'/^E\d+$/u',
			false,
			'illegal-entity-schema-title'
		);
		$validators[] = new WikiLinkExistsValidator(
			640
		);

		$topValidator = new DataValueValidator(
			new CompositeValidator( $validators )
		);

		return [ new TypeValidator( DataValue::class ), $topValidator ];
	}

	/**
	 * @param int $maxLength
	 * @return ValueValidator[]
	 */
	public function buildStringValidators( $maxLength = 400 ) {
		$validators = $this->getCommonStringValidators( $maxLength );

		$topValidator = new DataValueValidator(
			new CompositeValidator( $validators ) //Note: each validator is fatal
		);

		return [ new TypeValidator( DataValue::class ), $topValidator ];
	}

	/**
	 * @param int $maxLength Defaults to 400 characters. This was an arbitrary decision and simply copied the default
	 * of the CommonStringValidators
	 *
	 * @return ValueValidator[]
	 */
	public function buildMonolingualTextValidators( $maxLength = 400 ) {
		$validators = [];

		$validators[] = new DataFieldValidator(
			'text',
			new CompositeValidator( $this->getCommonStringValidators( $maxLength ) ) //Note: each validator is fatal
		);

		$validators[] = new DataFieldValidator(
			'language',
			new MembershipValidator( $this->contentLanguages->getLanguages(), 'not-a-language' )
		);

		$topValidator = new DataValueValidator(
			new CompositeValidator( $validators ) //Note: each validator is fatal
		);

		return [ new TypeValidator( DataValue::class ), $topValidator ];
	}

	/**
	 * @return ValueValidator[]
	 */
	public function buildTimeValidators() {
		$validators = [];
		$validators[] = new TypeValidator( 'array' );

		// Expected to be a short IRI, see TimeFormatter and TimeParser.
		$urlValidator = $this->getUrlValidator( [ 'http', 'https' ], $this->wikidataBaseUri, 255 );
		//TODO: enforce well known calendar models from config

		$validators[] = new DataFieldValidator( 'calendarmodel', $urlValidator );

		// time string field
		$timeStringValidators = [];
		$timeStringValidators[] = new TypeValidator( 'string' );

		// down to the day
		$maxPrecision = TimeValue::PRECISION_DAY;
		$isoDataPattern = '/T00:00:00Z\z/';

		$timeStringValidators[] = new RegexValidator( $isoDataPattern );

		$validators[] = new DataFieldValidator(
			'time',
			new CompositeValidator( $timeStringValidators ) //Note: each validator is fatal
		);

		$precisionValidators = [];
		$precisionValidators[] = new TypeValidator( 'integer' );
		$precisionValidators[] = new NumberRangeValidator( TimeValue::PRECISION_YEAR1G, $maxPrecision );

		$validators[] = new DataFieldValidator(
			'precision',
			new CompositeValidator( $precisionValidators ) //Note: each validator is fatal
		);
		$validators[] = new TimestampPrecisionValidator();

		$topValidator = new DataValueValidator(
			new CompositeValidator( $validators ) //Note: each validator is fatal
		);

		return [ new TypeValidator( DataValue::class ), $topValidator ];
	}

	/**
	 * @return ValueValidator[]
	 */
	public function buildCoordinateValidators() {
		$validators = [];
		$validators[] = new TypeValidator( 'array' );

		// Expected to be a short IRI, see GlobeCoordinateValue and GlobeCoordinateParser.
		$urlValidator = $this->getUrlValidator( [ 'http', 'https' ], $this->wikidataBaseUri, 255 );
		//TODO: enforce well known reference globes from config

		$validators[] = new DataFieldValidator( 'precision', new NumberValidator() );

		$validators[] = new DataFieldValidator( 'globe', $urlValidator );

		$topValidator = new DataValueValidator(
			new CompositeValidator( $validators ) //Note: each validator is fatal
		);

		return [ new TypeValidator( DataValue::class ), $topValidator ];
	}

	/**
	 * @param string[] $urlSchemes List of URL schemes, e.g. 'http'
	 * @param string|null $prefix a required prefix
	 * @param int $maxLength Defaults to 500 characters. Even if URLs are unlimited in theory they
	 * should be limited to about 2000. About 500 is a reasonable compromise.
	 * @see http://stackoverflow.com/a/417184
	 *
	 * @return CompositeValidator
	 */
	private function getUrlValidator( array $urlSchemes, $prefix = null, $maxLength = 500 ) {
		$validators = [];
		$validators[] = new TypeValidator( 'string' );
		$validators[] = new StringLengthValidator( 2, $maxLength );

		$urlValidatorsBuilder = new UrlSchemeValidators();
		$urlValidators = $urlValidatorsBuilder->getValidators( $urlSchemes );
		$validators[] = new UrlValidator( $urlValidators );

		if ( $prefix !== null ) {
			// FIXME: It's currently not possible to allow both http and https at this point.
			$validators[] = $this->getPrefixValidator( $prefix, 'bad-prefix' );
		}

		return new CompositeValidator( $validators ); //Note: each validator is fatal
	}

	/**
	 * @param string $prefix
	 * @param string $errorCode
	 *
	 * @return RegexValidator
	 */
	private function getPrefixValidator( $prefix, $errorCode ) {
		$regex = '/^' . preg_quote( $prefix, '/' ) . '/';
		return new RegexValidator( $regex, false, $errorCode );
	}

	/**
	 * @param int $maxLength
	 * @return ValueValidator[]
	 */
	public function buildUrlValidators( $maxLength = 500 ) {
		$urlValidator = $this->getUrlValidator( $this->urlSchemes, null, $maxLength );

		$topValidator = new DataValueValidator(
			$urlValidator
		);

		return [ new TypeValidator( DataValue::class ), $topValidator ];
	}

	/**
	 * @return ValueValidator[]
	 */
	public function buildQuantityValidators() {
		$validators = [];
		$validators[] = new TypeValidator( 'array' );

		// The "amount", "upperBound" and "lowerBound" fields are already validated by the
		// UnboundedQuantityValue/QuantityValue constructors.

		$unitValidators = new AlternativeValidator( [
			// NOTE: "1" is always considered legal for historical reasons,
			// since we use it to represent "unitless" quantities. We could also use
			// http://qudt.org/vocab/unit#Unitless or http://www.wikidata.org/entity/Q199
			new MembershipValidator( [ '1' ] ),
			$this->getUrlValidator( [ 'http', 'https' ], $this->itemVocabularyBaseUri, 255 ),
		] );
		$validators[] = new DataFieldValidator( 'unit', $unitValidators );

		$topValidator = new DataValueValidator(
			new CompositeValidator( $validators ) //Note: each validator is fatal
		);

		return [ new TypeValidator( UnboundedQuantityValue::class ), $topValidator ];
	}

}
