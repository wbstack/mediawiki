<?php

namespace MediaWiki\Extension\TemplateSandbox;

use InvalidArgumentException;
use MediaWiki\Content\Content;
use MediaWiki\Context\RequestContext;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\PageReference;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Revision\MutableRevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use Wikimedia\ScopedCallback;

/**
 * Business logic class for TemplateSandbox
 */
class Logic {
	/** @var int */
	private static $counter = 0;

	/** @var string[] Prefixes to search for sandbox templates */
	private $prefixes = [];

	/** @var Title|null Title to replace with $content */
	private $title = null;

	/** @var Content|null Content to replace $title */
	private $content = null;

	/**
	 * @param string[] $prefixes Title prefixes to search for sandbox templates
	 * @param Title|null $title Title to replace with 'content'
	 * @param Content|null $content Content to use to replace 'title'
	 */
	public function __construct( $prefixes, $title, $content ) {
		if ( ( $title === null ) !== ( $content === null ) ) {
			throw new InvalidArgumentException( '$title and $content must both be given or both be null' );
		}

		$this->prefixes = $prefixes;
		$this->title = $title;
		$this->content = $content;
	}

	/**
	 * Set up a ParserOptions for TemplateSandbox operation.
	 * @param ParserOptions $popt
	 * @return ScopedCallback to uninstall
	 */
	public function setupForParse( ParserOptions $popt ) {
		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();

		$inHook = false;
		$hookReset = $hookContainer->scopedRegister( 'TitleExists', function ( $title, &$exists ) use ( &$inHook ) {
			if ( $exists || $inHook ) {
				return;
			}
			$inHook = true;
			$titleText = $title->getPrefixedText();
			try {
				if ( $this->title && $this->title->equals( $title ) ) {
					$exists = true;
					return;
				}

				foreach ( $this->prefixes as $prefix ) {
					$newtitle = Title::newFromText( $prefix . '/' . $titleText );
					if ( $newtitle instanceof Title && $newtitle->exists() ) {
						$exists = true;
						return;
					}
				}
			} finally {
				$inHook = false;
			}
		} );

		$oldCurrentRevisionRecordCallback = $popt->setCurrentRevisionRecordCallback(
			function ( $title, $parser = false ) use ( &$oldCurrentRevisionRecordCallback ) {
				if ( $this->title && $this->title->equals( $title ) ) {
					$user = RequestContext::getMain()->getUser();
					$revRecord = new MutableRevisionRecord( $title );
					$revRecord->setUser( $user );
					$revRecord->setContent(
						SlotRecord::MAIN,
						$this->content
					);
					$revRecord->setParentId( $title->getLatestRevID() );
					return $revRecord;
				}

				foreach ( $this->prefixes as $prefix ) {
					$newtitle = Title::newFromText( $prefix . '/' . $title->getPrefixedText() );
					if ( $newtitle instanceof Title && $newtitle->exists() ) {
						$title = $newtitle;
						break;
					}
				}
				return call_user_func( $oldCurrentRevisionRecordCallback, $title, $parser );
			}
		);

		MediaWikiServices::getInstance()->getLinkCache()->clear();

		return new ScopedCallback( static function () use ( $hookReset ) {
			ScopedCallback::consume( $hookReset );
			MediaWikiServices::getInstance()->getLinkCache()->clear();
		} );
	}

	/**
	 * Add a handler for sandbox subpages to the OutputPage
	 * @param array $prefixes
	 * @param OutputPage $output
	 */
	public static function addSubpageHandlerToOutput( array $prefixes, OutputPage $output ) {
		$cache = [];
		$output->addContentOverrideCallback( static function ( $title ) use ( $prefixes, &$cache ) {
			/** @var PageReference|LinkTarget $title */
			$formatter = MediaWikiServices::getInstance()->getTitleFormatter();
			$titleText = $formatter->getPrefixedText( $title );
			if ( array_key_exists( $titleText, $cache ) ) {
				return $cache[$titleText];
			}
			foreach ( $prefixes as $prefix ) {
				$newtitle = Title::newFromText( $prefix . '/' . $titleText );
				if ( $newtitle instanceof Title && $newtitle->exists() ) {
					$rev = MediaWikiServices::getInstance()
						->getRevisionLookup()
						->getRevisionByTitle( $newtitle );
					$content = $rev ? $rev->getContent( SlotRecord::MAIN ) : null;
					if ( $content ) {
						$cache[$titleText] = $content;
						return $content;
					}
				}
			}
			$cache[$titleText] = null;
			return null;
		} );
	}

}
