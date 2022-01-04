<?php

namespace EntitySchema\MediaWiki\Actions;

use EntitySchema\MediaWiki\Content\EntitySchemaSlotDiffRenderer;
use EntitySchema\Presentation\ConfirmationFormRenderer;
use EntitySchema\Presentation\DiffRenderer;
use IContextSource;
use Page;

/**
 * @license GPL-2.0-or-later
 */
class UndoViewAction extends AbstractUndoAction {

	/** @var EntitySchemaSlotDiffRenderer */
	private $slotDiffRenderer;

	public function __construct(
		Page $page,
		EntitySchemaSlotDiffRenderer $slotDiffRenderer,
		IContextSource $context = null
	) {
		parent::__construct( $page, $context );
		$this->slotDiffRenderer = $slotDiffRenderer;
	}

	public function getName() {
		return 'edit';
	}

	public function show() {
		$this->getOutput()->enableOOUI();

		$this->getOutput()->setPageTitle(
			$this->msg(
				'entityschema-undo-heading',
				$this->getTitle()->getTitleValue()->getText()
			)
		);

		$req = $this->getContext()->getRequest();
		$diffStatus = $this->getDiffFromRequest( $req );
		if ( !$diffStatus->isOK() ) {
			$this->showUndoErrorPage( $diffStatus );
			return;
		}

		$patchStatus = $this->tryPatching( $diffStatus->getValue() );
		if ( !$patchStatus->isOK() ) {
			$this->showUndoErrorPage( $patchStatus );
			return;
		}

		$this->displayUndoDiff( $diffStatus->getValue() );
		$this->showConfirmationForm( $req->getInt( 'undo' ) );
	}

	/**
	 * Shows a form that can be used to confirm the requested undo/restore action.
	 *
	 * @param int $undidRevision
	 */
	private function showConfirmationForm( $undidRevision = 0 ) {
		$req = $this->getRequest();
		$renderer = new ConfirmationFormRenderer( $this );
		$confFormHTML = $renderer->showUndoRestoreConfirmationForm(
			[
				'undo' => $req->getInt( 'undo' ),
				'undoafter' => $req->getInt( 'undoafter' ),
			],
			'restore',
			$this->getTitle(),
			$this->getUser(),
			$undidRevision
		);

		$this->getOutput()->addHTML( $confFormHTML );
	}

	private function displayUndoDiff( $diff ) {

		$helper = new DiffRenderer( $this );
		$this->getOutput()->addHTML(
			$helper->renderSchemaDiffTable(
				$this->slotDiffRenderer->renderSchemaDiffRows( $diff ),
				$this->msg( 'entityschema-undo-old-revision' )
			)
		);

		$this->getOutput()->addModuleStyles( 'mediawiki.diff.styles' );
	}

}
