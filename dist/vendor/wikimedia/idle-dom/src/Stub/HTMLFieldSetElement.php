<?php

// AUTOMATICALLY GENERATED.  DO NOT EDIT.
// Use `composer build` to regenerate.

namespace Wikimedia\IDLeDOM\Stub;

use Exception;
use Wikimedia\IDLeDOM\HTMLCollection;
use Wikimedia\IDLeDOM\HTMLFormElement;
use Wikimedia\IDLeDOM\ValidityState;

trait HTMLFieldSetElement {

	// Underscore is used to avoid conflicts with DOM-reserved names
	// phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
	// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

	/**
	 * @return Exception
	 */
	abstract protected function _unimplemented(): Exception;

	// phpcs:enable

	/**
	 * @return HTMLFormElement|null
	 */
	public function getForm() {
		throw self::_unimplemented();
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		throw self::_unimplemented();
	}

	/**
	 * @return HTMLCollection
	 */
	public function getElements() {
		throw self::_unimplemented();
	}

	/**
	 * @return bool
	 */
	public function getWillValidate(): bool {
		throw self::_unimplemented();
	}

	/**
	 * @return ValidityState
	 */
	public function getValidity() {
		throw self::_unimplemented();
	}

	/**
	 * @return string
	 */
	public function getValidationMessage(): string {
		throw self::_unimplemented();
	}

	/**
	 * @return bool
	 */
	public function checkValidity(): bool {
		throw self::_unimplemented();
	}

	/**
	 * @return bool
	 */
	public function reportValidity(): bool {
		throw self::_unimplemented();
	}

	/**
	 * @param string $error
	 * @return void
	 */
	public function setCustomValidity( string $error ): void {
		throw self::_unimplemented();
	}

}
