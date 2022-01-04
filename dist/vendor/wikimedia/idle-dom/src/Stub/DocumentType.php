<?php

// AUTOMATICALLY GENERATED.  DO NOT EDIT.
// Use `composer build` to regenerate.

namespace Wikimedia\IDLeDOM\Stub;

use Exception;

trait DocumentType {
	// use \Wikimedia\IDLeDOM\Stub\ChildNode;

	// Underscore is used to avoid conflicts with DOM-reserved names
	// phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
	// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

	/**
	 * @return Exception
	 */
	abstract protected function _unimplemented(): Exception;

	// phpcs:enable

	/**
	 * @return string
	 */
	public function getName(): string {
		throw self::_unimplemented();
	}

	/**
	 * @return string
	 */
	public function getPublicId(): string {
		throw self::_unimplemented();
	}

	/**
	 * @return string
	 */
	public function getSystemId(): string {
		throw self::_unimplemented();
	}

}
