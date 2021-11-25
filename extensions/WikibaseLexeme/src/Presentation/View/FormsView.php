<?php

namespace Wikibase\Lexeme\Presentation\View;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\EntityId\EntityIdFormatter;
use Wikibase\DataModel\Term\Term;
use Wikibase\Lexeme\Domain\Model\Form;
use Wikibase\Lexeme\Domain\Model\FormSet;
use Wikibase\Lexeme\Presentation\View\Template\LexemeTemplateFactory;
use Wikibase\Lexeme\Presentation\View\Template\VueTemplates;
use Wikibase\View\LocalizedTextProvider;
use Wikibase\View\StatementGroupListView;
use WMDE\VueJsTemplating\Templating;

/**
 * @license GPL-2.0-or-later
 * @author Thiemo Kreuz
 */
class FormsView {

	/**
	 * @var LocalizedTextProvider
	 */
	private $textProvider;

	/**
	 * @var LexemeTemplateFactory
	 */
	private $templateFactory;

	/**
	 * @var EntityIdFormatter
	 */
	private $entityIdFormatter;

	/**
	 * @var StatementGroupListView
	 */
	private $statementGroupListView;

	public function __construct(
		LocalizedTextProvider $textProvider,
		LexemeTemplateFactory $templateFactory,
		EntityIdFormatter $entityIdFormatter,
		StatementGroupListView $statementGroupListView
	) {
		$this->textProvider = $textProvider;
		$this->templateFactory = $templateFactory;
		$this->entityIdFormatter = $entityIdFormatter;
		$this->statementGroupListView = $statementGroupListView;
	}

	/**
	 * @param FormSet $forms
	 *
	 * @return string HTML
	 */
	public function getHtml( FormSet $forms ) {
		$html = '<div class="wikibase-lexeme-forms-section">';
		$html .= '<h2 class="wb-section-heading section-heading">'
			. '<span class="mw-headline" id="forms">'
			. htmlspecialchars( $this->textProvider->get( 'wikibaselexeme-header-forms' ) )
			. '</span>'
			. '</h2>';

		$html .= '<div class="wikibase-lexeme-forms ">';
		foreach ( $forms->toArray() as $form ) {
			$html .= $this->getFormHtml( $form );
		}
		$html .= '</div>'; // wikibase-lexeme-forms
		// @phan-suppress-next-line PhanPluginDuplicateAdjacentStatement
		$html .= '</div>'; // wikibase-lexeme-forms-section
		return $html;
	}

	/**
	 * @param Form $form
	 *
	 * @return string HTML
	 */
	private function getFormHtml( Form $form ) {
		$grammaticalFeaturesHtml = $this->templateFactory->render(
			'wikibase-lexeme-form-grammatical-features',
			[
				htmlspecialchars(
					$this->textProvider->get( 'wikibaselexeme-form-grammatical-features' ),
					ENT_QUOTES,
					'UTF-8',
					false
				),
				implode(
					// Escape HTML without double escaping entities, {@see Message::escaped}
					htmlspecialchars( $this->textProvider->get( 'comma-separator' ), ENT_QUOTES, 'UTF-8', false ),
					array_map(
						function ( ItemId $id ) {
							return '<span>' . $this->getGrammaticalFeatureHtml( $id ) . '</span>';
						},
						$form->getGrammaticalFeatures()
					)
				)
			]
		);

		return $this->templateFactory->render( 'wikibase-lexeme-form', [
			htmlspecialchars( $form->getId()->getSerialization() ),
			$this->renderRepresentationWidget( $form ),
			$grammaticalFeaturesHtml,
			$this->getStatementSectionHtml( $form ),
			// Anchor separated from ID to avoid issue with front-end rendering
			htmlspecialchars( $form->getId()->getIdSuffix() )
		] );
	}

	/**
	 * @return string
	 */
	private function renderRepresentationWidget( Form $form ) {
		$templating = new Templating();
		$representationsVueTemplate = file_get_contents( __DIR__ . VueTemplates::REPRESENTATIONS );

		$representations = array_map(
			static function ( Term $r ) {
				return [ 'value' => $r->getText(), 'language' => $r->getLanguageCode() ];
			},
			iterator_to_array( $form->getRepresentations() )
		);

		$result = $templating->render(
			$representationsVueTemplate,
			[
				'inEditMode' => false,
				'representations' => $representations
			],
			[
				'message' => function ( $key ) {
					return $this->textProvider->get( $key );
				}
			]
		);

		return '<div class="form-representations">'
			. $result
			. '</div>';
	}

	/**
	 * @param ItemId $id
	 *
	 * @return string HTML
	 */
	private function getGrammaticalFeatureHtml( ItemId $id ) {
		return $this->entityIdFormatter->formatEntityId( $id );
	}

	/**
	 * @param Form $form
	 *
	 * @return string HTML
	 */
	private function getStatementSectionHtml( Form $form ) {
		$headerText = htmlspecialchars(
			$this->textProvider->get(
				'wikibaselexeme-statementsection-statements-about-form',
				[ $form->getId()->getSerialization() ]
			)
		);

		$statementHeader = <<<HTML
<h2 class="wb-section-heading section-heading wikibase-statements" dir="auto">
	<span class="mw-headline">{$headerText}</span>
</h2>
HTML;

		$statementSection = $this->statementGroupListView->getHtml(
			$form->getStatements()->toArray(), $form->getId()->getIdSuffix()
		);
		return $statementHeader . $statementSection;
	}

}
