<?php

declare( strict_types = 1 );

namespace Wikibase\Repo\Localizer;

use Exception;
use InvalidArgumentException;
use MediaWiki\Message\Message;

/**
 * ExceptionLocalizer implementing localization of some well known types of exceptions
 * that may occur in the context of the Wikibase exception, as provided in $localizers.
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 * @author Katie Filbert < aude.wiki@gmail.com >
 */
class DispatchingExceptionLocalizer implements ExceptionLocalizer {

	/**
	 * @var ExceptionLocalizer[]
	 */
	private array $localizers;

	/**
	 * @param ExceptionLocalizer[] $localizers
	 */
	public function __construct( array $localizers ) {
		$this->localizers = $localizers;
	}

	public function getExceptionMessage( Exception $exception ): Message {
		$localizer = $this->getLocalizerForException( $exception );

		if ( $localizer ) {
			return $localizer->getExceptionMessage( $exception );
		}

		throw new InvalidArgumentException( 'ExceptionLocalizer not registered for exception type.' );
	}

	public function hasExceptionMessage( Exception $exception ): bool {
		$localizer = $this->getLocalizerForException( $exception );

		return $localizer ? true : false;
	}

	private function getLocalizerForException( Exception $exception ): ?ExceptionLocalizer {
		foreach ( $this->localizers as $localizer ) {
			if ( $localizer->hasExceptionMessage( $exception ) ) {
				return $localizer;
			}
		}

		return null;
	}

}
