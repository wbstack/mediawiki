<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;

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

	public function __construct() {
		parent::__construct( 'TemplateSandbox' );
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
			$output->wrapWikiMsg( "<div class='previewnote errorbox'>\n$1\n</div>", $error );
		} elseif ( $this->output !== null ) {
			// Wrap output in a div for proper language markup.
			$pageLang = $this->title->getPageViewLanguage();
			$attribs = [ 'lang' => $pageLang->getHtmlCode(), 'dir' => $pageLang->getDir(),
				'class' => 'mw-content-' . $pageLang->getDir() ];
			$this->output->setText( Html::rawElement( 'div', $attribs, $this->output->getRawText() ) );

			// Anons have predictable edit tokens, only do the JS/CSS preview for logged-in users.
			if ( $user->isAnon() ) {
				$output->wrapWikiMsg(
					"<div class='previewnote warningbox'>\n$1\n</div>", 'templatesandbox-anon-limited-preview'
				);
			} else {
				TemplateSandboxLogic::addSubpageHandlerToOutput( $this->prefixes, $output );
			}
			$output->addParserOutput( $this->output );

			// @phan-suppress-next-line SecurityCheck-XSS
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
	 * @return bool|String
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
	 * @return bool|String
	 */
	public function validateRevidParam( $value, $allData ) {
		if ( $value === '' || $value === null ) {
			return true;
		}

		$revisionRecord = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionById( $value );
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
	 * @return bool|String
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
		$services = MediaWikiServices::getInstance();
		$revisionLookup = $services->getRevisionLookup();
		if ( $data['revid'] !== '' && $data['revid'] !== null ) {
			$rev = $revisionLookup->getRevisionById( $data['revid'] );
			$title = Title::newFromLinkTarget( $rev->getPageAsLinkTarget() );
		} elseif ( $data['page'] !== '' && $data['page'] !== null ) {
			$title = Title::newFromText( $data['page'] );
			$rev = $revisionLookup->getRevisionByTitle( $title );
		} else {
			return Status::newFatal( 'templatesandbox-page-or-revid' );
		}

		if ( $data['text'] !== '' && $data['text'] !== null ) {
			$content = $services->getContentHandlerFactory()
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

		$page = WikiPage::factory( $title );
		$popts = $page->makeParserOptions( $this->getContext() );
		$popts->setIsPreview( true );
		$popts->setIsSectionPreview( false );
		$logic = new TemplateSandboxLogic( $this->prefixes, null, null );
		$reset = $logic->setupForParse( $popts );
		$this->title = $title;
		$this->output = $content->getParserOutput( $title, $rev->getId(), $popts );

		return Status::newGood();
	}
}
