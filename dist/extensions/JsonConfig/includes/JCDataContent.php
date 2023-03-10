<?php

namespace JsonConfig;

use Html;
use Language;
use MediaWiki\Page\PageReference;
use Parser;
use ParserOptions;
use stdClass;

/**
 * @package JsonConfig
 */
abstract class JCDataContent extends JCObjContent {

	/**
	 * Derived classes must implement this method to perform custom validation
	 * using the check(...) calls
	 */
	public function validateContent() {
		if ( !$this->thorough() ) {
			// We are not doing any modifications to the original, so no need to validate it
			return;
		}

		$this->test( 'license', JCValidators::isStringLine(), self::isValidLicense() );
		$this->testOptional( 'description', [ 'en' => '' ], JCValidators::isLocalizedString() );
		$this->testOptional( 'sources', '', JCValidators::isString() );
	}

	/** Returns a validator function to check if the value is a valid string
	 * @return callable
	 */
	public static function isValidLicense() {
		return static function ( JCValue $v, array $path ) {
			global $wgJsonConfigAllowedLicenses, $wgLang;
			if ( !in_array( $v->getValue(), $wgJsonConfigAllowedLicenses, true ) ) {
				$v->error( 'jsonconfig-err-license', $path,
					$wgLang->commaList( $wgJsonConfigAllowedLicenses ) );
				return false;
			}
			return true;
		};
	}

	/**
	 * Get data as localized for the given language
	 * @param Language $lang
	 * @return mixed
	 */
	public function getLocalizedData( Language $lang ) {
		if ( !$this->isValid() ) {
			return null;
		}
		$result = (object)[];
		$this->localizeData( $result, $lang );
		return $result;
	}

	/**
	 * Resolve any override-specific localizations, and add it to $result
	 * @param stdClass $result
	 * @param Language $lang
	 */
	protected function localizeData( $result, Language $lang ) {
		$data = $this->getData();
		if ( property_exists( $data, 'description' ) ) {
			// @phan-suppress-next-line PhanTypeMismatchArgument
			$result->description = JCUtils::pickLocalizedString( $data->description, $lang );
		}
		$license = $this->getLicenseObject();
		if ( $license ) {
			$text = $license['text']->inLanguage( $lang )->plain();
			$result->license = (object)[
				'code' => $license['code'],
				'text' => $text,
				'url' => $license['url']->inLanguage( $lang )->plain(),
			];
		}
		if ( property_exists( $data, 'sources' ) ) {
			$result->sources = $data->sources;
		}
	}

	public function renderDescription( $lang ) {
		$description = $this->getField( 'description' );

		if ( $description && !$description->error() ) {
			$description = JCUtils::pickLocalizedString( $description->getValue(), $lang );
			$html = Html::element( 'p', [ 'class' => 'mw-jsonconfig-description' ], $description );
		} else {
			$html = '';
		}

		return $html;
	}

	/**
	 * Renders license HTML, including optional "or later version" clause
	 *     <a href="...">Creative Commons 1.0</a>, or later version
	 * @return string
	 */
	public function renderLicense() {
		$license = $this->getLicenseObject();
		if ( $license ) {
			$text = Html::element( 'a', [
				'href' => $license['url']->plain()
			], $license['text']->plain() );

			$text = wfMessage( 'jsonconfig-license' )->rawParams( $text )->parse();

			$html = Html::rawElement( 'p', [ 'class' => 'mw-jsonconfig-license' ], $text );
		} else {
			$html = '';
		}

		return $html;
	}

	/**
	 * Get the license object of the content.
	 * license code is identifier from https://spdx.org/licenses/
	 * @return array|false code=>license code text=>license name url=>license URL
	 */
	public function getLicenseObject() {
		$license = $this->getField( 'license' );
		if ( $license && !$license->error() ) {
			// should be a valid license identifier as in https://spdx.org/licenses/
			$code = $license->getValue();

			return [
				'code' => $code,
				'text' => wfMessage( 'jsonconfig-license-name-' . $code ),
				'url' => wfMessage( 'jsonconfig-license-url-' . $code ),
			];
		}
		return false;
	}

	public function renderSources( Parser $parser, PageReference $page, $revId, ParserOptions $options ) {
		$sources = $this->getField( 'sources' );

		if ( $sources && !$sources->error() ) {
			$markup = $sources->getValue();
			$html = Html::rawElement( 'p', [ 'class' => 'mw-jsonconfig-sources' ],
				$parser->parse( $markup, $page, $options, true, true, $revId )->getRawText() );
		} else {
			$html = '';
		}

		return $html;
	}
}
