<?php
/**
 * ThatSrc extension Hooks
 *
 * @file
 * @ingroup Extensions
 */

namespace ThatSrc;

use Wikibase\Repo\Validators\StringLengthValidator;
use Wikibase\Repo\Validators\TypeValidator;


function getMultilineValidator( $maxLength = 50*1024 ) {
	$validators = [];

	$validators[] = new TypeValidator( 'string' );
	$validators[] = new StringLengthValidator( 1, $maxLength, 'mb_strlen' );

	return $validators;
}



call_user_func( function() {


});
