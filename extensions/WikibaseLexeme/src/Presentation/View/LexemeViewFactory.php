<?php

namespace Wikibase\Lexeme\Presentation\View;

use Language;
use Wikibase\Lexeme\Presentation\Formatters\LexemeTermFormatter;
use Wikibase\Lexeme\Presentation\View\Template\LexemeTemplateFactory;
use Wikibase\Lexeme\WikibaseLexemeServices;
use Wikibase\Lib\TermLanguageFallbackChain;
use Wikibase\Repo\MediaWikiLanguageDirectionalityLookup;
use Wikibase\Repo\MediaWikiLocalizedTextProvider;
use Wikibase\Repo\View\RepoSpecialPageLinker;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\View\Template\TemplateFactory;
use Wikibase\View\ToolbarEditSectionGenerator;

/**
 * @license GPL-2.0-or-later
 * @author Thiemo Kreuz
 */
class LexemeViewFactory {

	/**
	 * @var TermLanguageFallbackChain
	 */
	private $termFallbackChain;

	/**
	 * @var Language
	 */
	private $language;

	public function __construct(
		Language $language,
		TermLanguageFallbackChain $termFallbackChain
	) {
		$this->termFallbackChain = $termFallbackChain;
		$this->language = $language;
	}

	public function newLexemeView() {
		$templates = include __DIR__ . '/../../../resources/templates.php';
		$templateFactory = new LexemeTemplateFactory( $templates );

		$languageDirectionalityLookup = new MediaWikiLanguageDirectionalityLookup();
		$localizedTextProvider = new MediaWikiLocalizedTextProvider( $this->language );

		$wikibaseRepo = WikibaseRepo::getDefaultInstance();

		$editSectionGenerator = $this->newToolbarEditSectionGenerator();

		$languageNameLookup = WikibaseLexemeServices::getLanguageNameLookup();

		$statementSectionsView = $wikibaseRepo->getViewFactory()->newStatementSectionsView(
			$this->language->getCode(),
			$this->termFallbackChain,
			$editSectionGenerator
		);

		$statementGroupListView = $wikibaseRepo->getViewFactory()->newStatementGroupListView(
			$this->language->getCode(),
			$this->termFallbackChain,
			$editSectionGenerator
		);

		$idLinkFormatter = $wikibaseRepo->getEntityIdHtmlLinkFormatterFactory()
			->getEntityIdFormatter( $this->language );

		$formsView = new FormsView(
			$localizedTextProvider,
			$templateFactory,
			$idLinkFormatter,
			$statementGroupListView
		);

		$sensesView = new SensesView(
			$localizedTextProvider,
			$languageDirectionalityLookup,
			$templateFactory,
			$statementGroupListView,
			$languageNameLookup
		);

		return new LexemeView(
			TemplateFactory::getDefaultInstance(),
			$languageDirectionalityLookup,
			$this->language->getCode(),
			$formsView,
			$sensesView,
			$statementSectionsView,
			new LexemeTermFormatter(
				$localizedTextProvider
					->get( 'wikibaselexeme-presentation-lexeme-display-label-separator-multiple-lemma' )
			),
			$idLinkFormatter
		);
	}

	private function newToolbarEditSectionGenerator() {
		return new ToolbarEditSectionGenerator(
			new RepoSpecialPageLinker(),
			TemplateFactory::getDefaultInstance(),
			new MediaWikiLocalizedTextProvider( $this->language )
		);
	}

}
