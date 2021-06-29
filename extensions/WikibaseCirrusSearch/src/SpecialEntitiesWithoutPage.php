<?php

namespace Wikibase\Search\Elastic;

use HTMLForm;
use Title;
use Wikibase\Lib\ContentLanguages;
use Wikibase\Lib\LanguageNameLookup;
use Wikibase\Lib\Store\EntityNamespaceLookup;
use Wikibase\Repo\Specials\SpecialWikibaseQueryPage;

/**
 * Base page for pages listing entities without a specific value.
 *
 * @license GPL-2.0-or-later
 * @author Thomas Pellissier Tanon
 * @author Bene* < benestar.wikimedia@gmail.com >
 * @author Thiemo Kreuz
 */
class SpecialEntitiesWithoutPage extends SpecialWikibaseQueryPage {

	/**
	 * @var string Language code as requested by the user.
	 */
	private $requestedLanguageCode = '';

	/**
	 * @var string|null Entity type identifier as requested by the user.
	 */
	private $requestedEntityType = '';

	/**
	 * @var string One of the TermIndexEntry::TYPE_... constants, provided on construction time.
	 */
	private $termType;

	/**
	 * @var string
	 */
	private $legendMsg;

	/**
	 * @var string[] List of entity type identifiers the special page should accept, provided on
	 *  construction time.
	 */
	private $acceptableEntityTypes;

	/**
	 * @var ContentLanguages
	 */
	private $termsLanguages;

	/**
	 * @var LanguageNameLookup
	 */
	private $languageNameLookup;

	/**
	 * @var EntityNamespaceLookup
	 */
	private $entityNamespaceLookup;

	/**
	 * @param string $name
	 * @param string $termType One of the TermIndexEntry::TYPE_... constants.
	 * @param string $legendMsg
	 * @param string[] $acceptableEntityTypes
	 * @param ContentLanguages $termsLanguages
	 * @param LanguageNameLookup $languageNameLookup
	 * @param EntityNamespaceLookup $entityNamespaceLookup
	 */
	public function __construct(
		$name,
		$termType,
		$legendMsg,
		array $acceptableEntityTypes,
		ContentLanguages $termsLanguages,
		LanguageNameLookup $languageNameLookup,
		EntityNamespaceLookup $entityNamespaceLookup
	) {
		parent::__construct( $name );

		$this->termType = $termType;
		$this->legendMsg = $legendMsg;
		$this->acceptableEntityTypes = $acceptableEntityTypes;
		$this->termsLanguages = $termsLanguages;
		$this->languageNameLookup = $languageNameLookup;
		$this->entityNamespaceLookup = $entityNamespaceLookup;
	}

	/**
	 * @see \Wikibase\Repo\Specials\SpecialWikibasePage::execute
	 *
	 * @param string|null $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$this->prepareArguments( $subPage );
		$this->setForm();

		if ( $this->requestedLanguageCode !== '' && $this->requestedEntityType !== '' ) {
			$this->showQuery(
				[],
				$this->requestedLanguageCode . '/' . $this->requestedEntityType
			);
		}
	}

	/**
	 * Prepare the arguments
	 *
	 * @param string|null $subPage
	 */
	private function prepareArguments( $subPage ) {
		$this->requestedLanguageCode = '';
		$this->requestedEntityType = '';

		if ( $subPage !== null ) {
			$parts = explode( '/', $subPage, 2 );
			$this->requestedLanguageCode = $parts[0];
			if ( isset( $parts[1] ) ) {
				$this->requestedEntityType = $parts[1];
			}
		}

		$request = $this->getRequest();
		$this->requestedLanguageCode = $request->getText( 'language', $this->requestedLanguageCode );
		$this->requestedEntityType = $request->getText( 'type', $this->requestedEntityType );

		if ( $this->requestedLanguageCode !== ''
			&& !$this->termsLanguages->hasLanguage( $this->requestedLanguageCode )
		) {
			$this->showErrorHTML( $this->msg(
				'wikibasecirrus-entitieswithoutlabel-invalid-language',
				wfEscapeWikiText( $this->requestedLanguageCode )
			)->parse() );
			$this->requestedLanguageCode = '';
		}

		if ( $this->requestedEntityType !== ''
			&& !in_array( $this->requestedEntityType, $this->acceptableEntityTypes )
		) {
			$this->showErrorHTML( $this->msg(
				'wikibasecirrus-entitieswithoutlabel-invalid-type',
				wfEscapeWikiText( $this->requestedEntityType )
			)->parse() );
			$this->requestedEntityType = '';
		}
	}

	/**
	 * Return options for the language input field.
	 *
	 * @return string[]
	 */
	private function getLanguageOptions() {
		$options = [];

		foreach ( $this->termsLanguages->getLanguages() as $languageCode ) {
			$languageName = $this->languageNameLookup->getName( $languageCode );
			$options["$languageName ($languageCode)"] = $languageCode;
		}

		return $options;
	}

	/**
	 * Build the HTML form
	 */
	private function setForm() {
		$entityTypeOptions = [];
		foreach ( $this->acceptableEntityTypes as $type ) {
			// Messages:
			// wikibasecirrus-entity-item
			// wikibasecirrus-entity-property
			$msg = $this->msg( 'wikibasecirrus-entity-' . $type );
			$text = $msg->isDisabled() ? $type : $msg->text();
			$entityTypeOptions[$text] = $type;
		}

		$formDescriptor = [
			'language' => [
				'name' => 'language',
				'default' => $this->requestedLanguageCode,
				'type' => 'combobox',
				'options' => $this->getLanguageOptions(),
				'cssclass' => 'wb-language-suggester',
				'id' => 'wb-entitieswithoutpage-language',
				'label-message' => 'wikibasecirrus-entitieswithoutlabel-label-language'
			],
			'type' => [
				'name' => 'type',
				'options' => $entityTypeOptions,
				'default' => $this->requestedEntityType ?: reset( $this->acceptableEntityTypes ),
				'type' => 'select',
				'id' => 'wb-entitieswithoutpage-type',
				'label-message' => 'wikibasecirrus-entitieswithoutlabel-label-type'
			],
			'submit' => [
				'name' => '',
				'default' => $this->msg( 'wikibasecirrus-entitieswithoutlabel-submit' )->text(),
				'type' => 'submit',
				'id' => 'wikibasecirrus-entitieswithoutpage-submit',
			]
		];

		HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() )
			->setId( 'wb-entitieswithoutpage-form' )
			->setMethod( 'get' )
			->setWrapperLegendMsg( $this->legendMsg )
			->suppressDefaultSubmit()
			->setSubmitCallback( function () {
				// no-op
			} )
			->show();
	}

	/**
	 * @see SpecialWikibaseQueryPage::getResult
	 *
	 * @param int $offset
	 * @param int $limit
	 * @return array empty array
	 */
	protected function getResult( $offset = 0, $limit = 0 ) {
		$title = Title::makeTitle( NS_SPECIAL, 'Search' );
		$ns = $this->entityNamespaceLookup->getEntityNamespace( $this->requestedEntityType );
		$url = $title->getFullUrlForRedirect( "sort=relevance&search=-has{$this->termType}:{$this->requestedLanguageCode}&ns$ns=1" );
		$this->getOutput()->redirect( $url );

		return [];
	}

	/**
	 * @inheritDoc
	 */
	protected function getTitleForNavigation() {
		return $this->getPageTitle( $this->requestedLanguageCode . '/' . $this->requestedEntityType );
	}

}
