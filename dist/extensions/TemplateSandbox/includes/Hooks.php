<?php

namespace MediaWiki\Extension\TemplateSandbox;

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiExpandTemplates;
use MediaWiki\Api\ApiParse;
use MediaWiki\Api\Hook\APIGetAllowedParamsHook;
use MediaWiki\Api\Hook\ApiMakeParserOptionsHook;
use MediaWiki\Config\Config;
use MediaWiki\Content\Content;
use MediaWiki\Content\IContentHandlerFactory;
use MediaWiki\Content\Renderer\ContentRenderer;
use MediaWiki\Content\Transform\ContentTransformer;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\EditPage\EditPage;
use MediaWiki\Hook\AlternateEditPreviewHook;
use MediaWiki\Hook\EditPage__importFormDataHook;
use MediaWiki\Hook\EditPage__showStandardInputs_optionsHook;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Html\Html;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Request\WebRequest;
use MediaWiki\ResourceLoader as RL;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\Widget\TitleInputWidget;
use MediaWiki\Xml\Xml;
use MWContentSerializationException;
use OOUI\ActionFieldLayout;
use OOUI\ButtonInputWidget;
use OOUI\FieldsetLayout;
use OOUI\HtmlSnippet;
use OOUI\Layout;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ScopedCallback;

class Hooks implements
	EditPage__importFormDataHook,
	EditPage__showStandardInputs_optionsHook,
	AlternateEditPreviewHook,
	APIGetAllowedParamsHook,
	ApiMakeParserOptionsHook
{
	/** @var int */
	private static $counter = 0;

	private IContentHandlerFactory $contentHandlerFactory;
	private ContentRenderer $contentRenderer;
	private ContentTransformer $contentTransformer;
	private HookContainer $hookContainer;
	private UserOptionsLookup $userOptionsLookup;
	private WikiPageFactory $wikiPageFactory;

	public function __construct(
		IContentHandlerFactory $contentHandlerFactory,
		ContentRenderer $contentRenderer,
		ContentTransformer $contentTransformer,
		HookContainer $hookContainer,
		UserOptionsLookup $userOptionsLookup,
		WikiPageFactory $wikiPageFactory
	) {
		$this->contentHandlerFactory = $contentHandlerFactory;
		$this->contentRenderer = $contentRenderer;
		$this->contentTransformer = $contentTransformer;
		$this->hookContainer = $hookContainer;
		$this->userOptionsLookup = $userOptionsLookup;
		$this->wikiPageFactory = $wikiPageFactory;
	}

	/**
	 * Hook for EditPage::importFormData to parse our new form fields, and if
	 * necessary put $editpage into "preview" mode.
	 *
	 * Note we specifically do not check $wgTemplateSandboxEditNamespaces here,
	 * since users can manually enable this for other namespaces.
	 *
	 * @param EditPage $editpage
	 * @param WebRequest $request
	 */
	public function onEditPage__importFormData( $editpage, $request ) {
		$editpage->templatesandbox_template = $request->getText(
			'wpTemplateSandboxTemplate', $editpage->getTitle()->getPrefixedText()
		);
		$editpage->templatesandbox_page = $request->getText( 'wpTemplateSandboxPage' );

		if ( $request->wasPosted() ) {
			if ( $request->getCheck( 'wpTemplateSandboxPreview' ) ) {
				$editpage->templatesandbox_preview = true;
				$editpage->preview = true;
				$editpage->save = false;
				$editpage->live = false;
			}
		}
	}

	/**
	 * @param IContextSource $context
	 * @param string $msg
	 * @return string
	 */
	private static function wrapErrorMsg( IContextSource $context, $msg ) {
		return "<div id='mw-$msg'>\n"
			. $context->msg( $msg )->parseAsBlock()
			. "\n</div>";
	}

	/**
	 * Hook for AlternateEditPreview to output an entirely different preview
	 * when our button was clicked.
	 *
	 * @param EditPage $editpage
	 * @param Content &$content
	 * @param string &$out
	 * @param ParserOutput &$parserOutput
	 * @return bool
	 */
	public function onAlternateEditPreview( $editpage, &$content, &$out,
		&$parserOutput
	) {
		if ( !isset( $editpage->templatesandbox_preview ) ) {
			return true;
		}

		$context = $editpage->getContext();

		if ( $editpage->templatesandbox_template === '' ||
			$editpage->templatesandbox_template === null
		) {
			$out = self::wrapErrorMsg( $context, 'templatesandbox-editform-need-template' );
			return false;
		}
		if ( $editpage->templatesandbox_page === '' || $editpage->templatesandbox_page === null ) {
			$out = self::wrapErrorMsg( $context, 'templatesandbox-editform-need-title' );
			return false;
		}

		$templatetitle = Title::newFromText( $editpage->templatesandbox_template );
		if ( !$templatetitle instanceof Title ) {
			$out = self::wrapErrorMsg( $context, 'templatesandbox-editform-invalid-template' );
			return false;
		}

		$title = Title::newFromText( $editpage->templatesandbox_page );
		if ( !$title instanceof Title ) {
			$out = self::wrapErrorMsg( $context, 'templatesandbox-editform-invalid-title' );
			return false;
		}

		// If we're previewing the same page we're editing, we don't need to check whether
		// we exist, since we fake that we exist later. This is useful to, for example,
		// preview a page move.
		if ( !$title->equals( $templatetitle ) && !$title->exists() ) {
			$out = self::wrapErrorMsg( $context, 'templatesandbox-editform-title-not-exists' );
			return false;
		}

		$note = '';
		$dtitle = false;
		$parserOutput = null;

		$user = $context->getUser();
		$output = $context->getOutput();
		$lang = $context->getLanguage();

		try {
			if ( $editpage->sectiontitle !== '' ) {
				// TODO (T314475): If sectiontitle is null this uses '' rather than summary; is that wanted?
				$sectionTitle = $editpage->sectiontitle ?? '';
			} else {
				$sectionTitle = $editpage->summary;
			}

			if ( $editpage->getArticle()->getPage()->exists() ) {
				$content = $editpage->getArticle()->getPage()->replaceSectionContent(
					$editpage->section, $content, $sectionTitle, $editpage->edittime
				);
				if ( $content === null ) {
					$out = self::wrapErrorMsg( $context, 'templatesandbox-failed-replace-section' );
					return false;
				}
			} else {
				if ( $editpage->section === 'new' ) {
					$content = $content->addSectionHeader( $sectionTitle );
				}
			}

			// Apply PST to the to-be-saved text
			$popts = $editpage->getArticle()->getPage()->makeParserOptions(
				$context
			);
			$popts->setIsPreview( true );
			$popts->setIsSectionPreview( false );
			$content = $this->contentTransformer->preSaveTransform(
				$content,
				$templatetitle,
				$user,
				$popts
			);

			$note = $context->msg( 'templatesandbox-previewnote', $title->getPrefixedText() )->plain() .
				' [[#' . EditPage::EDITFORM_ID . '|' . $lang->getArrow() . ' ' .
				$context->msg( 'continue-editing' )->text() . ']]';

			$page = $this->wikiPageFactory->newFromTitle( $title );
			$popts = $page->makeParserOptions( $context );
			$popts->setIsPreview( true );
			$popts->setIsSectionPreview( false );
			$logic = new Logic( [], $templatetitle, $content );
			$reset = $logic->setupForParse( $popts );

			$revRecord = call_user_func_array(
				$popts->getCurrentRevisionRecordCallback(),
				[ $title ]
			);

			$pageContent = $revRecord->getContent(
				SlotRecord::MAIN,
				RevisionRecord::FOR_THIS_USER,
				$user
			);
			$parserOutput = $this->contentRenderer->getParserOutput( $pageContent, $title, $revRecord, $popts );

			$output->addParserOutputMetadata( $parserOutput );
			if ( $output->userCanPreview() ) {
				$output->addContentOverride( $templatetitle, $content );
			}

			$dtitle = $parserOutput->getDisplayTitle();
			$parserOutput->setTitleText( '' );
			$skinOptions = $output->getSkin()->getOptions();
			$out = $parserOutput->getText( [
				'injectTOC' => $skinOptions['toc'],
				'enableSectionEditLinks' => false,
				'includeDebugInfo' => true,
			] );

			if ( count( $parserOutput->getWarnings() ) ) {
				$note .= "\n\n" . implode( "\n\n", $parserOutput->getWarnings() );
			}
		} catch ( MWContentSerializationException $ex ) {
			$m = $context->msg( 'content-failed-to-parse',
				$editpage->contentModel, $editpage->contentFormat, $ex->getMessage()
			);
			$note .= "\n\n" . $m->parse();
			$out = '';
		}

		$dtitle = $dtitle === false ? $title->getPrefixedText() : $dtitle;
		$previewhead = Html::rawElement(
			'div', [ 'class' => 'previewnote' ],
			Html::rawElement(
				'h2', [ 'id' => 'mw-previewheader' ],
				$context->msg( 'templatesandbox-preview', $title->getPrefixedText(), $dtitle )->parse()
			) .
			Html::warningBox(
				$output->parseAsInterface( $note )
			)
		);

		$out = $previewhead . $out . $editpage->previewTextAfterContent;

		return false;
	}

	/**
	 * Hook for EditPage::showStandardInputs:options to add our form fields to
	 * the "editOptions" area of the page.
	 *
	 * @param EditPage $editpage
	 * @param OutputPage $output
	 * @param int &$tabindex
	 */
	public function onEditPage__showStandardInputs_options( $editpage, $output, &$tabindex ) {
		$namespaces = array_merge(
			$output->getConfig()->get( 'TemplateSandboxEditNamespaces' ),
			ExtensionRegistry::getInstance()->getAttribute( 'TemplateSandboxEditNamespaces' )
		);

		$contentModels = ExtensionRegistry::getInstance()->getAttribute(
			'TemplateSandboxEditContentModels' );

		// Show the form if the title is in an allowed namespace, has an allowed content model
		// or if the user requested it with &wpTemplateSandboxShow
		$showForm = $editpage->getTitle()->inNamespaces( $namespaces )
			|| in_array( $editpage->getTitle()->getContentModel(), $contentModels, true )
			|| $output->getRequest()->getCheck( 'wpTemplateSandboxShow' );

		if ( !$showForm ) {
			// output the values in hidden fields so that a user
			// using a gadget doesn't have to re-enter them every time

			$html = Xml::openElement( 'span', [ 'id' => 'templatesandbox-editform' ] );

			$html .= Html::hidden( 'wpTemplateSandboxTemplate',
				$editpage->templatesandbox_template, [ 'id' => 'wpTemplateSandboxTemplate' ]
			);

			$html .= Html::hidden( 'wpTemplateSandboxPage',
				$editpage->templatesandbox_page, [ 'id' => 'wpTemplateSandboxPage' ]
			);

			$html .= Xml::closeElement( 'span' );

			$output->addHTML( $html . "\n" );

			return;
		}

		$output->addModuleStyles( 'ext.TemplateSandbox.top' );
		$output->addModules( 'ext.TemplateSandbox' );

		$context = $editpage->getContext();

		$textHtml = '';
		$text = $context->msg( 'templatesandbox-editform-text' );
		if ( !$text->isDisabled() ) {
			$textAttrs = [
				'class' => 'mw-templatesandbox-editform-text',
			];
			$textHtml = Xml::tags( 'div', $textAttrs, $text->parse() );
		}

		$helptextHtml = '';
		$helptext = $context->msg( 'templatesandbox-editform-helptext' );
		if ( !$helptext->isDisabled() ) {
			$helptextAttrs = [
				'class' => 'mw-templatesandbox-editform-helptext',
			];
			$helptextHtml = Xml::tags( 'span', $helptextAttrs, $helptext->parse() );
		}

		$hiddenInputsHtml =
			Html::hidden( 'wpTemplateSandboxTemplate',
				$editpage->templatesandbox_template, [ 'id' => 'wpTemplateSandboxTemplate' ]
			) .
			// If they submit our form, pass the parameter along for not allowed namespaces
			Html::hidden( 'wpTemplateSandboxShow', '' );

		$output->enableOOUI();
		$output->addModules( 'oojs-ui-core' );
		$output->addModules( 'mediawiki.widgets' );

		$fieldsetLayout =
			new FieldsetLayout( [
				'label' => new HtmlSnippet( $context->msg( 'templatesandbox-editform-legend' )->parse() ),
				'id' => 'templatesandbox-editform',
				'classes' => [ 'mw-templatesandbox-fieldset' ],
				'items' => [
					// TODO: OOUI should provide a plain content layout, as this is
					// technically an abstract class
					new Layout( [
						'content' => new HtmlSnippet( $textHtml . "\n" . $hiddenInputsHtml )
					] ),
					new ActionFieldLayout(
						new TitleInputWidget( [
							'id' => 'wpTemplateSandboxPage',
							'name' => 'wpTemplateSandboxPage',
							'value' => $editpage->templatesandbox_page,
							'tabIndex' => ++$tabindex,
							'placeholder' => $context->msg( 'templatesandbox-editform-page-label' )->text(),
							'infusable' => true,
						] ),
						new ButtonInputWidget( [
							'id' => 'wpTemplateSandboxPreview',
							'name' => 'wpTemplateSandboxPreview',
							'label' => $context->msg( 'templatesandbox-editform-view-label' )->text(),
							'tabIndex' => ++$tabindex,
							'type' => 'submit',
							'useInputTag' => true,
						] ),
						[ 'align' => 'top' ]
					)
				]
			] );

		if ( $helptextHtml ) {
			$fieldsetLayout->addItems( [
				// TODO: OOUI should provide a plain content layout, as this is
				// technically an abstract class
				new Layout( [
					'content' => new HtmlSnippet( $helptextHtml )
				] )
			] );
		}
		$output->addHTML( $fieldsetLayout );

		if ( $this->userOptionsLookup->getOption( $context->getUser(), 'uselivepreview' ) ) {
			$output->addModules( 'ext.TemplateSandbox.preview' );
		}
	}

	/**
	 * Determine if this API module is appropriate for us to mess with.
	 * @param ApiBase $module
	 * @return bool
	 */
	private static function isUsableApiModule( $module ) {
		return $module instanceof ApiParse || $module instanceof ApiExpandTemplates;
	}

	/**
	 * Hook for APIGetAllowedParams to add our API parameters to the relevant
	 * modules.
	 *
	 * @param ApiBase $module
	 * @param array &$params
	 * @param int $flags
	 */
	public function onAPIGetAllowedParams( $module, &$params, $flags ) {
		if ( !self::isUsableApiModule( $module ) ) {
			return;
		}

		$params += [
			'templatesandboxprefix' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_ISMULTI => true,
				ApiBase::PARAM_HELP_MSG => 'templatesandbox-apihelp-prefix',
			],
			'templatesandboxtitle' => [
				ParamValidator::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'templatesandbox-apihelp-title',
			],
			'templatesandboxtext' => [
				ParamValidator::PARAM_TYPE => 'text',
				ApiBase::PARAM_HELP_MSG => 'templatesandbox-apihelp-text',
			],
			'templatesandboxcontentmodel' => [
				ParamValidator::PARAM_TYPE => $this->contentHandlerFactory->getContentModels(),
				ApiBase::PARAM_HELP_MSG => 'templatesandbox-apihelp-contentmodel',
			],
			'templatesandboxcontentformat' => [
				ParamValidator::PARAM_TYPE => $this->contentHandlerFactory->getAllContentFormats(),
				ApiBase::PARAM_HELP_MSG => 'templatesandbox-apihelp-contentformat',
			],
		];
	}

	/**
	 * Hook for ApiMakeParserOptions to set things up for TemplateSandbox
	 * parsing when necessary.
	 *
	 * @param ParserOptions $options
	 * @param Title $title
	 * @param array $params
	 * @param ApiBase $module
	 * @param null &$reset Set to a ScopedCallback used to reset any hooks set.
	 * @param bool &$suppressCache
	 */
	public function onApiMakeParserOptions(
		$options, $title, $params, $module, &$reset, &$suppressCache
	) {
		// Shouldn't happen, but...
		if ( !self::isUsableApiModule( $module ) ) {
			return;
		}

		$params += [
			'templatesandboxprefix' => [],
			'templatesandboxtitle' => null,
			'templatesandboxtext' => null,
			'templatesandboxcontentmodel' => null,
			'templatesandboxcontentformat' => null,
		];
		$params = [
			// @phan-suppress-next-line PhanImpossibleCondition
			'prefix' => $params['templatesandboxprefix'] ?: [],
			'title' => $params['templatesandboxtitle'],
			'text' => $params['templatesandboxtext'],
			'contentmodel' => $params['templatesandboxcontentmodel'],
			'contentformat' => $params['templatesandboxcontentformat'],
		];

		if ( ( $params['title'] === null ) !== ( $params['text'] === null ) ) {
			$p = $module->getModulePrefix();
			$module->dieWithError( [ 'templatesandbox-apierror-titleandtext', $p ], 'invalidparammix' );
		}

		$prefixes = [];
		foreach ( $params['prefix'] as $prefix ) {
			$prefixTitle = Title::newFromText( rtrim( $prefix, '/' ) );
			if ( !$prefixTitle instanceof Title || $prefixTitle->getFragment() !== '' ||
				$prefixTitle->isExternal()
			) {
				$p = $module->getModulePrefix();
				$module->dieWithError(
					[ 'apierror-badparameter', "{$p}templatesandboxprefix" ], "bad_{$p}templatesandboxprefix"
				);
			}
			$prefixes[] = $prefixTitle->getPrefixedText();
		}

		if ( $params['title'] !== null ) {
			$page = $module->getTitleOrPageId( $params );
			if ( $params['contentmodel'] == '' ) {
				$contentHandler = $page->getContentHandler();
			} else {
				$contentHandler = $this->contentHandlerFactory
					// @phan-suppress-next-line PhanTypeMismatchArgumentNullable
					->getContentHandler( $params['contentmodel'] );
			}

			$escName = wfEscapeWikiText( $page->getTitle()->getPrefixedDBkey() );
			$model = $contentHandler->getModelID();

			if ( $contentHandler->supportsDirectApiEditing() === false ) {
				$module->dieWithError( [ 'apierror-no-direct-editing', $model, $escName ] );
			}

			// @phan-suppress-next-line PhanImpossibleCondition
			$format = $params['contentformat'] ?: $contentHandler->getDefaultFormat();
			if ( !$contentHandler->isSupportedFormat( $format ) ) {
				$module->dieWithError( [ 'apierror-badformat', $format, $model, $escName ] );
			}

			$templatetitle = $page->getTitle();
			$content = $contentHandler->makeContent( $params['text'], $page->getTitle(), $model, $format );

			// Apply PST to templatesandboxtext
			$popts = $page->makeParserOptions( $module );
			$popts->setIsPreview( true );
			$popts->setIsSectionPreview( false );
			$user = RequestContext::getMain()->getUser();
			$content = $this->contentTransformer->preSaveTransform(
				$content,
				$templatetitle,
				$user,
				$popts
			);
		} else {
			$templatetitle = null;
			$content = null;
		}

		if ( $prefixes || $templatetitle ) {
			$logic = new Logic( $prefixes, $templatetitle, $content );
			$resetLogic = $logic->setupForParse( $options );
			$suppressCache = true;

			$resetHook = $this->hookContainer->scopedRegister(
				'ApiParseMakeOutputPage',
				static function ( $module, $output )
					use ( $prefixes, $templatetitle, $content )
				{
					if ( $prefixes ) {
						Logic::addSubpageHandlerToOutput( $prefixes, $output );
					}
					if ( $templatetitle ) {
						$output->addContentOverride( $templatetitle, $content );
					}
				}
			);

			$reset = new ScopedCallback( static function () use ( &$resetLogic, &$resetHook ) {
				ScopedCallback::consume( $resetHook );
				ScopedCallback::consume( $resetLogic );
			} );
		}
	}

	/**
	 * Function that returns an array of parsed messages used in live preview
	 * for the ResourceLoader
	 *
	 * @param RL\Context $context
	 * @return array
	 */
	public static function getParsedMessages( $context ) {
		return [
			'templatesandbox-previewnote' => $context->msg( 'templatesandbox-previewnote' )->parse(),
		];
	}

	/**
	 * Function that returns an array of valid namespaces to show the page
	 * preview form on for the ResourceLoader
	 *
	 * @param RL\Context $context
	 * @param Config $config
	 * @return array
	 */
	public static function getTemplateNamespaces( $context, $config ) {
		return array_merge(
			$config->get( 'TemplateSandboxEditNamespaces' ),
			ExtensionRegistry::getInstance()->getAttribute( 'TemplateSandboxEditNamespaces' )
		);
	}

}
