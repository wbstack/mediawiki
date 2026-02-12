'use strict';

$( () => {
	$( '<input>' ).attr( {
		type: 'hidden',
		name: 'mw-twocolconflict-js',
		value: 1
	} ).prependTo( '#editform' );
} );
