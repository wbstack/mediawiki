( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.util = mw.libs.advancedSearch.util || {};

	mw.libs.advancedSearch.util.arrayEquals = function ( a1, a2 ) {
		var i = a1.length;
		if ( i !== a2.length ) {
			return false;
		}
		while ( i-- ) {
			if ( a1[ i ] !== a2[ i ] ) {
				return false;
			}
		}
		return true;
	};

	mw.libs.advancedSearch.util.arrayContains = function ( a1, a2 ) {
		return $( a1 ).filter( a2 ).length === a2.length;
	};

	mw.libs.advancedSearch.util.arrayConcatUnique = function ( a1, a2 ) {
		return a1.concat( a2.filter( function ( item ) {
			return a1.indexOf( item ) === -1;
		} ) );
	};

}() );
