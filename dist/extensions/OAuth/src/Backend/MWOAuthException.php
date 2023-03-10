<?php

namespace MediaWiki\Extension\OAuth\Backend;

use MediaWiki\Extension\OAuth\Lib\OAuthException;

class MWOAuthException extends OAuthException {
	/** @var string */
	public $msg;
	/** @var array|null */
	public $params;

	/**
	 * Exception that may be shown to an end user
	 * @param string $msg Message key (string) for error text
	 * @param array|null $params with parameters to wfMessage()
	 */
	public function __construct( $msg, $params = null ) {
		$this->msg = $msg;
		$this->params = $params;
		parent::__construct(
			wfMessage( $msg, $params )->inLanguage( 'en' )->useDatabase( false )->plain()
		);
	}

}
