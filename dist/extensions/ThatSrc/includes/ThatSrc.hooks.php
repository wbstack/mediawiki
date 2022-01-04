<?php
/**
 * ThatSrc extension Hooks
 *
 * @file
 * @ingroup Extensions
 */



namespace ThatSrc;


use DataValues\DataValue;
use DataValues\StringValue;
use ValueFormatters\FormatterOptions;
use ValueFormatters\StringFormatter;
use ValueFormatters\ValueFormatter;
use ValueParsers\ParserOptions;
use ValueParsers\StringParser;
use ValueValidators\ValueValidator;
use Wikibase\Lib\Formatters\SnakFormat;
use Wikibase\Lib\Formatters\SnakFormatter;
use Wikibase\Rdf\DedupeBag;
use Wikibase\Rdf\EntityMentionListener;
use Wikibase\Rdf\RdfVocabulary;
use Wikibase\Rdf\Values\LiteralValueRdfBuilder;
use Wikibase\Repo\Validators\DataValueValidator;
use Wikibase\Repo\Validators\StringLengthValidator;
use Wikibase\Repo\Validators\TypeValidator;
use Wikibase\Repo\WikibaseRepo;
use Wikimedia\Purtle\RdfWriter;

class Hooks
{

	/**
	 * @param int $maxLength
	 * @return ValueValidator[]
	 */
	private static function buildMultilineTextValidators( $maxLength = 1024*1024 ) {
		return [
			new TypeValidator( DataValue::class ),
			new DataValueValidator(
				new StringLengthValidator(1, $maxLength, 'mb_strlen')
			)
		];
	}

	/**
	 * @param string $format The desired target format, see SnakFormatter::FORMAT_XXX
	 * @param FormatterOptions $options
	 *
	 * @return ValueFormatter
	 */
	public static function newMultilineTextFormatter( $format, FormatterOptions $options ) {
		$snakFormat = new SnakFormat();

		switch ( $snakFormat->getBaseFormat( $format ) ) {
			case SnakFormatter::FORMAT_HTML:
				return new HtmlMultilineTextFormatter( $options );
			case SnakFormatter::FORMAT_WIKI:
				// Use the string formatter without escaping!
				return new StringFormatter( $options );
			default:
				return WikibaseRepo::getDefaultValueFormatterBuilders()->newStringFormatter( $format, $options );
		}
	}


	/**
	 * Add Datatype "Math" to the Wikibase Repository
	 * @param array[] &$dataTypeDefinitions
	 */
	public static function onWikibaseRepoDataTypes(array &$dataTypeDefinitions)
	{
		$dataTypeDefinitions['PT:multilinetext'] = [
			'value-type' => 'string',
			'expert-module' => 'ext.ThatSrc.MultilineTextValue',
			'validator-factory-callback' => function() {
				return Hooks::buildMultilineTextValidators();
			},
			'parser-factory-callback' => function( ParserOptions $options ) {
				$normalizer = WikibaseRepo::getDefaultInstance()->getStringNormalizer();
				return new StringParser( new WikibaseMultilineTextValueNormalizer( $normalizer ) );
			},
			'formatter-factory-callback' => function( $format, FormatterOptions $options ) {
				return Hooks::newMultilineTextFormatter( $format, $options );
			},
			'rdf-builder-factory-callback' => function (
				$flags,
				RdfVocabulary $vocab,
				RdfWriter $writer,
				EntityMentionListener $tracker,
				DedupeBag $dedupe
			) {
				return new LiteralValueRdfBuilder( null, null );
			},
			'search-index-data-formatter-callback' => function ( StringValue $value ) {
				// TODO: MultilineTextValue ?
				return $value->getValue();
			},
		];
	}

}
