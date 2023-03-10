<?php

namespace MediaWiki\Extension\OAuth\Frontend;

use LogEntry;
use LogFormatter;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use Message;
use Title;

/**
 * Formatter for OAuth log events
 */
class OAuthLogFormatter extends LogFormatter {
	/** @var LinkRenderer */
	protected $linkRenderer;

	protected function __construct( LogEntry $entry ) {
		parent::__construct( $entry );
		$this->linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
	}

	protected function getMessageParameters() {
		$params = parent::getMessageParameters();
		if ( isset( $params[3] ) ) {
			// @phan-suppress-next-line SecurityCheck-DoubleEscaped
			$params[3] = $this->getConsumerLink( $params[3] );
		}
		return $params;
	}

	protected function getConsumerLink( $consumerKey ) {
		$title = Title::newFromText( 'Special:OAuthListConsumers/view/' . $consumerKey );
		if ( $this->plaintext ) {
			return '[[' . $title->getPrefixedText() . '|' . $consumerKey . ']]';
		} else {
			return Message::rawParam( $this->linkRenderer->makeLink( $title, $consumerKey ) );
		}
	}
}
