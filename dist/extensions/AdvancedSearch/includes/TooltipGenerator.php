<?php

namespace AdvancedSearch;

use MessageLocalizer;

/**
 * Generate HTML tooltips from messages
 *
 * This is a workaround for the deficiencies of mw.message( 'keyname' ).parse() which does not
 * support HTML except italic and bold and does not convert wiki text.
 *
 * See https://phabricator.wikimedia.org/T27349
 *
 * @license GPL-2.0-or-later
 */
class TooltipGenerator {

	private const MESSAGE_KEYS = [
		'advancedsearch-help-plain',
		'advancedsearch-help-phrase',
		'advancedsearch-help-or',
		'advancedsearch-help-not',
		'advancedsearch-help-deepcategory',
		'advancedsearch-help-hastemplate',
		'advancedsearch-help-inlanguage',
		'advancedsearch-help-intitle',
		'advancedsearch-help-subpageof',
		'advancedsearch-help-filetype',
		'advancedsearch-help-filew',
		'advancedsearch-help-fileh',
		'advancedsearch-help-sort'
	];

	private MessageLocalizer $messageLocalizer;

	public function __construct( MessageLocalizer $messageLocalizer ) {
		$this->messageLocalizer = $messageLocalizer;
	}

	/**
	 * @return string[]
	 */
	public function generateTooltips(): array {
		$tooltips = [];

		foreach ( self::MESSAGE_KEYS as $key ) {
			$msg = $this->messageLocalizer->msg( $key );
			if ( !$msg->isDisabled() ) {
				$tooltips[$key] = $msg->parse();
			}
		}

		return $tooltips;
	}

}
