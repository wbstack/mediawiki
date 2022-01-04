'use strict';

function initJsCheck() {
	$( '<input>' ).attr( {
		type: 'hidden',
		name: 'mw-twocolconflict-js',
		value: true
	} ).prependTo( '#editform' );
}

$( function () {
	initJsCheck();
} );
