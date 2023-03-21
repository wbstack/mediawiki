<?php

namespace MediaWiki\Extension\TemplateSandbox;

use Content;
use EditPage;
use Html;
use HTMLForm;
use MediaWiki\Content\IContentHandlerFactory;
use MediaWiki\Content\Renderer\ContentRenderer;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use ParserOutput;
use SpecialPage;
use Status;
use Title;

class SpecialTemplateSandbox extends SpecialPage {
	private $prefixes = [];

	/**
	 * @var null|Title
	 */
	private $title = null;

	/**
	 * @var null|ParserOutput
	 */
	private $output = null;

	/** @var RevisionLookup */
	private $revisionLookup;

	/** @var IContentHandlerFactory */
	private $contentHandlerFactory;

	/** @var WikiPageFactory */
	private $wikiPageFactory;

	/** @var ContentRenderer */
	private $contentRenderer;

	/**
	 * @param RevisionLookup $revisionLookup
	 * @param IContentHandlerFactory $contentHandlerFactory
	 * @param WikiPageFactory $wikiPageFactory
	 * @param ContentRenderer $contentRenderer
	 */
	public function __construct(
		RevisionLookup $revisionLookup,
		IContentHandlerFactory $contentHandlerFactory,
		WikiPageFactory $wikiPageFactory,
		ContentRenderer $contentRenderer
	) {
		parent::__construct( 'TemplateSandbox' );
		$this->revisionLookup = $revisionLookup;
		$this->contentHandlerFactory = $contentHandlerFactory;
		$this->wikiPageFactory = $wikiPageFactory;
		$this->contentRenderer = $contentRenderer;
	}

	protected function getGroupName() {
		return 'wiki';
	}

	public function execute( $par ) {
		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:TemplateSandbox' );
		$this->checkPermissions();

		$request = $this->getRequest();

		if ( $par !== null && !$request->getCheck( 'page' ) ) {
			$request->setVal( 'page', $par );
		}

		$default_prefix = Title::makeTitle( NS_USER,
			$this->getUser()->getName() . '/' . $this->msg( 'templatesandbox-suffix' )->plain()
		)->getPrefixedText();

		$form = HTMLForm::factory( 'ooui', [
			'prefix' => [
				'type' => 'text',
				'name' => 'prefix',
				'default' => $default_prefix,
				'label-message' => 'templatesandbox-prefix-label',
				'validation-callback' => [ $this, 'validatePrefixParam' ],
			],

			'page' => [
				'type' => 'text',
				'name' => 'page',
				'label-message' => 'templatesandbox-page-label',
				'validation-callback' => [ $this, 'validatePageParam' ],
			],

			'revid' => [
				'type' => 'int',
				'name' => 'revid',
				'label-message' => 'templatesandbox-revid-label',
				'validation-callback' => [ $this, 'validateRevidParam' ],
			],

			'text' => [
				'type' => 'textarea',
				'name' => 'text',
				'label-message' => 'templatesandbox-text-label',
				'useeditfont' => true,
				'rows' => 5,
			],
		], $this->getContext() );
		$form->setSubmitCallback( [ $this, 'onSubmit' ] );
		$form->setWrapperLegendMsg( 'templatesandbox-legend' );
		$form->addHeaderText( $this->msg( 'templatesandbox-text' )->parseAsBlock() );
		$form->setSubmitTextMsg( 'templatesandbox-submit' );

		$form->prepareForm();
		if ( $request->getCheck( 'page' ) || $request->getCheck( 'revid' ) ) {
			$form->displayForm( $form->tryAuthorizedSubmit() );
		} else {
			$form->displayForm( false );
		}

		$user = $this->getUser();
		$output = $this->getOutput();
		$error = false;
		if ( $this->getRequest()->wasPosted() ) {
			if ( $user->isAnon() && !$user->isAllowed( 'edit' ) ) {
				$error = 'templatesandbox-fail-post-anon';
			} elseif ( !$user->matchEditToken( $request->getVal( 'wpEditToken' ), '', $request ) ) {
				$error = 'templatesandbox-fail-post';
			}
		}
		if ( $error !== false ) {
			$output->addHTML(
				Html::errorBox(
					$output->msg( $error )->parse(),
					'',
					'previewnote'
				)
			);
		} elseif ( $this->output !== null ) {
			// Wrap output in a div for proper language markup.
			$pageLang = $this->title->getPageViewLanguage();
			$attribs = [ 'lang' => $pageLang->getHtmlCode(), 'dir' => $pageLang->getDir(),
				'class' => 'mw-content-' . $pageLang->getDir() ];
			$this->output->setText( Html::rawElement( 'div', $attribs, $this->output->getRawText() ) );

			// Anons have predictable edit tokens, only do the JS/CSS preview for logged-in users.
			if ( $user->isAnon() ) {
				$output->addHTML(
					Html::warningBox(
						$output->msg( 'templatesandbox-anon-limited-preview' )->parse(),
						'previewnote'
					)
				);
			} else {
				Logic::addSubpageHandlerToOutput( $this->prefixes, $output );
			}
			$output->addParserOutput( $this->output );

			$output->addHTML( Html::rawElement(
				'div',
				[ 'class' => 'limitreport', 'style' => 'clear:both' ],
				EditPage::getPreviewLimitReport( $this->output )
			) );
			$output->addModules( 'mediawiki.collapseFooterLists' );

			$titleText = $this->output->getTitleText();
			if ( strval( $titleText ) !== '' ) {
				$output->setPageTitle( $this->msg( 'templatesandbox-title-output', $titleText ) );
			}
		}
	}

	/**
	 * @param string|null $value
	 * @param array $allData
	 * @return bool|string
	 */
	public function validatePageParam( $value, $allData ) {
		if ( $value === '' || $value === null ) {
			return true;
		}
		$title = Title::newFromText( $value );
		if ( !$title instanceof Title ) {
			return $this->msg( 'templatesandbox-invalid-title' )->parseAsBlock();
		}
		if ( !$title->exists() ) {
			return $this->msg( 'templatesandbox-title-not-exists' )->parseAsBlock();
		}
		return true;
	}

	/**
	 * @param string|null $value
	 * @param array $allData
	 * @return bool|string
	 */
	public function validateRevidParam( $value, $allData ) {
		if ( $value === '' || $value === null ) {
			return true;
		}

		$revisionRecord = $this->revisionLookup->getRevisionById( (int)$value );
		if ( $revisionRecord === null ) {
			return $this->msg( 'templatesandbox-revision-not-exists' )->parseAsBlock();
		}

		$content = $revisionRecord->getContent(
			SlotRecord::MAIN,
			RevisionRecord::FOR_THIS_USER,
			$this->getUser()
		);

		if ( $content === null ) {
			return $this->msg( 'templatesandbox-revision-no-content' )->parseAsBlock();
		}
		return true;
	}

	/**
	 * @param string|null $value
	 * @param array $allData
	 * @return bool|string
	 */
	public function validatePrefixParam( $value, $allData ) {
		if ( $value === '' || $value === null ) {
			return true;
		}
		$prefixes = array_map( 'trim', explode( '|', $value ) );
		foreach ( $prefixes as $prefix ) {
			$title = Title::newFromText( rtrim( $prefix, '/' ) );
			if ( !$title instanceof Title || $title->getFragment() !== '' ) {
				return $this->msg( 'templatesandbox-invalid-prefix' )->parseAsBlock();
			}
			if ( $title->isExternal() ) {
				return $this->msg( 'templatesandbox-prefix-not-local' )->parseAsBlock();
			}
			$this->prefixes[] = $title->getPrefixedText();
		}
		return true;
	}

	/**
	 * @param array $data
	 * @param HTMLForm $form
	 * @return Status
	 */
	public function onSubmit( $data, $form ) {
		if ( $data['revid'] !== '' && $data['revid'] !== null ) {
			$rev = $this->revisionLookup->getRevisionById( $data['revid'] );
			$title = Title::newFromLinkTarget( $rev->getPageAsLinkTarget() );
		} elseif ( $data['page'] !== '' && $data['page'] !== null ) {
			$title = Title::newFromText( $data['page'] );
			$rev = $this->revisionLookup->getRevisionByTitle( $title );
		} else {
			return Status::newFatal( 'templatesandbox-page-or-revid' );
		}

		if ( $data['text'] !== '' && $data['text'] !== null ) {
			$content = $this->contentHandlerFactory
				->getContentHandler( $rev->getSlot( SlotRecord::MAIN )->getModel() )
				->unserializeContent( $data['text'] );
		} else {
			$content = $rev->getContent(
				SlotRecord::MAIN,
				RevisionRecord::FOR_THIS_USER,
				$this->getUser()
			);
		}

		// Title and Content are validated by validatePrefixParam and validatePageParam
		'@phan-var Title $title';
		'@phan-var Content $content';

		$page = $this->wikiPageFactory->newFromTitle( $title );
		$popts = $page->makeParserOptions( $this->getContext() );
		$popts->setIsPreview( true );
		$popts->setIsSectionPreview( false );
		$logic = new Logic( $this->prefixes, null, null );
		$reset = $logic->setupForParse( $popts );
		$this->title = $title;
		$this->output = $this->contentRenderer->getParserOutput( $content, $title, $rev->getId(), $popts );

		return Status::newGood();
	}
}
