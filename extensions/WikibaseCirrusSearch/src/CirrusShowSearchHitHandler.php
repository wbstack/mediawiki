<?php

namespace Wikibase\Search\Elastic;

use Html;
use HtmlArmor;
use IContextSource;
use SearchResult;
use SpecialSearch;
use Title;
use Wikibase\Lib\Store\EntityIdLookup;
use Wikibase\Repo\Hooks\Formatters\EntityLinkFormatter;
use Wikibase\Repo\Hooks\ShowSearchHitHandler;
use Wikibase\Repo\WikibaseRepo;

/**
 * Handler to format entities in the search results
 *
 * @license GPL-2.0-or-later
 * @author Matěj Suchánek
 * @author Daniel Kinzler
 * @author Stas Malyshev
 */
class CirrusShowSearchHitHandler {

	/**
	 * @var EntityLinkFormatter
	 */
	private $linkFormatter;

	/**
	 * @var EntityIdLookup
	 */
	private $entityIdLookup;

	/**
	 * @param EntityIdLookup $entityIdLookup
	 * @param EntityLinkFormatter $linkFormatter
	 */
	public function __construct(
		EntityIdLookup $entityIdLookup,
		EntityLinkFormatter $linkFormatter
	) {
		$this->entityIdLookup = $entityIdLookup;
		$this->linkFormatter = $linkFormatter;
	}

	/**
	 * @param IContextSource $context
	 * @return self
	 */
	private static function newFromGlobalState( IContextSource $context ) {
		$wikibaseRepo = WikibaseRepo::getDefaultInstance();

		return new self(
			WikibaseRepo::getEntityIdLookup(),
			$wikibaseRepo->getEntityLinkFormatterFactory( $context->getLanguage() )->getDefaultLinkFormatter()
		);
	}

	/**
	 * Format the output when the search result contains entities
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ShowSearchHit
	 * @see showEntityResultHit
	 */
	public static function onShowSearchHit( SpecialSearch $searchPage, SearchResult $result,
		array $terms, &$link, &$redirect, &$section, &$extract, &$score, &$size, &$date, &$related,
		&$html
	) {
		if ( $result instanceof EntityResult ) {
			$self = self::newFromGlobalState( $searchPage->getContext() );
			$self->showEntityResultHit( $searchPage, $result, $terms,
				$link, $redirect, $section, $extract, $score, $size, $date, $related, $html );
		}
	}

	/**
	 * Show result hit which is the result of Cirrus-driven entity search.
	 */
	private function showEntityResultHit( SpecialSearch $searchPage, EntityResult $result, array $terms,
		&$link, &$redirect, &$section, &$extract, &$score, &$size, &$date, &$related, &$html
	) {
		$extract = '';
		$displayLanguage = $searchPage->getLanguage()->getCode();
		// Put highlighted description of the item as the extract
		ShowSearchHitHandler::addDescription( $extract, $result->getDescriptionHighlightedData(), $searchPage );
		// Add extra data
		$extra = $result->getExtraDisplay();
		if ( $extra ) {
			$attr = [ 'class' => 'wb-itemlink-description' ];
			$extra = ShowSearchHitHandler::withLanguage( $extra, $displayLanguage );
			ShowSearchHitHandler::addLanguageAttrs( $attr, $displayLanguage, $extra );
			$section = $searchPage->msg( 'colon-separator' )->escaped();
			$section .= Html::rawElement( 'span', $attr, HtmlArmor::getHtml( $extra['value'] ) );
		}
		// set $size to size metrics
		$size = $searchPage->msg(
			'wikibase-search-result-stats',
			$result->getStatementCount(),
			$result->getSitelinkCount()
		)->escaped();
	}

	/**
	 * Remove span tag (added by Cirrus) placed around title search hit for entity titles
	 * to highlight matches in bold.
	 *
	 * @todo Add highlighting when Q##-id matches and not label text.
	 *
	 * @param Title $title
	 * @param string &$titleSnippet
	 * @param SearchResult $result
	 * @param string $terms
	 * @param SpecialSearch $specialSearch
	 * @param string[] &$query
	 * @param string[] &$attributes
	 */
	public static function onShowSearchHitTitle(
		Title $title,
		&$titleSnippet,
		SearchResult $result,
		$terms,
		SpecialSearch $specialSearch,
		array &$query,
		array &$attributes
	) {
		if ( $result instanceof EntityResult ) {
			$self = self::newFromGlobalState( $specialSearch->getContext() );
			$self->getEntityLink( $result, $title, $titleSnippet, $attributes,
				$specialSearch->getLanguage()->getCode() );
		}
	}

	/**
	 * Generate link text for Title link in search hit.
	 * @param EntityResult $result
	 * @param Title $title
	 * @param string|HtmlArmor &$html Variable where HTML will be placed
	 * @param array &$attributes Link tag attributes, can add more
	 * @param string $displayLanguage
	 */
	private function getEntityLink(
		EntityResult $result,
		Title $title,
		&$html,
		&$attributes,
		$displayLanguage
	) {
		$entityId = $this->entityIdLookup->getEntityIdForTitle( $title );
		if ( !$entityId ) {
			return;
		}
		// Highlighter already encodes and marks up the HTML
		$html = new HtmlArmor(
			$this->linkFormatter->getHtml( $entityId,
				ShowSearchHitHandler::withLanguage( $result->getLabelHighlightedData(), $displayLanguage )
			)
		);

		$attributes['title'] = $this->linkFormatter->getTitleAttribute(
			$entityId,
			$result->getLabelData(),
			$result->getDescriptionData()
		);
	}

}
