<?php

namespace JsonConfig;

use MediaWiki\Config\Config;
use MediaWiki\Content\IContentHandlerFactory;
use MediaWiki\Extension\CodeEditor\Hooks\CodeEditorGetPageLanguageHook;
use MediaWiki\Title\Title;

/**
 * Hook handlers for JsonConfig extension.
 * All hooks from the CodeEditor extension which is optional to use with this extension.
 *
 * @file
 * @ingroup Extensions
 * @ingroup JsonConfig
 * @license GPL-2.0-or-later
 */
class CodeEditorHooks implements
	CodeEditorGetPageLanguageHook
{
	private Config $config;
	private IContentHandlerFactory $contentHandlerFactory;

	public function __construct(
		Config $config,
		IContentHandlerFactory $contentHandlerFactory
	) {
		$this->config = $config;
		$this->contentHandlerFactory = $contentHandlerFactory;
	}

	/**
	 * Declares JSON as the code editor language for Config: pages.
	 * This hook only runs if the CodeEditor extension is enabled.
	 * @param Title $title The title the language is for
	 * @param string|null &$lang The language to use
	 * @param string $model The content model of the title
	 * @param string $format The content format of the title
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onCodeEditorGetPageLanguage( Title $title, ?string &$lang, string $model, string $format ) {
		if ( !JCHooks::jsonConfigIsStorage( $this->config ) ) {
			return;
		}

		// todo/fixme? We should probably add 'json' lang to only those pages that pass parseTitle()
		$handler = $this->contentHandlerFactory->getContentHandler( $title->getContentModel() );
		if ( $handler->getDefaultFormat() === CONTENT_FORMAT_JSON || JCSingleton::parseTitle( $title ) ) {
			$lang = 'json';
		}
	}
}
