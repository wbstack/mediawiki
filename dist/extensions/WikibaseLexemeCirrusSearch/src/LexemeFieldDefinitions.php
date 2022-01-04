<?php

namespace Wikibase\Lexeme\Search\Elastic;

use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Services\Lookup\EntityLookup;
use Wikibase\Repo\Search\Fields\FieldDefinitions;
use Wikibase\Repo\Search\Fields\WikibaseIndexField;

/**
 * @license GPL-2.0-or-later
 * @author Katie Filbert < aude.wiki@gmail.com >
 */
class LexemeFieldDefinitions implements FieldDefinitions {

	/**
	 * @var EntityLookup
	 */
	private $entityLookup;

	/**
	 * @var NumericPropertyId|null
	 */
	private $lexemeLanguageCodePropertyId;
	/**
	 * @var FieldDefinitions
	 */
	private $statements;

	public function __construct( FieldDefinitions $statements,
								 EntityLookup $entityLookup,
								 NumericPropertyId $lexemeLanguageCodePropertyId = null ) {
		$this->statements = $statements;
		$this->lexemeLanguageCodePropertyId = $lexemeLanguageCodePropertyId;
		$this->entityLookup = $entityLookup;
	}

	/**
	 * @return WikibaseIndexField[]
	 */
	public function getFields() {
		$fields = $this->statements->getFields();

		$fields[LemmaField::NAME] = new LemmaField();
		$fields[FormsField::NAME] = new FormsField();
		$fields[LexemeLanguageField::NAME] = new LexemeLanguageField( $this->entityLookup,
			$this->lexemeLanguageCodePropertyId );
		$fields[LexemeCategoryField::NAME] = new LexemeCategoryField();
		return $fields;
	}

}
