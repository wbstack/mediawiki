'use strict';

/**
 * @param {Array} a1
 * @param {Array} a2
 * @return {boolean}
 */
const arrayContains = function ( a1, a2 ) {
	return $( a1 ).filter( a2 ).length === a2.length;
};

/**
 * @param {Array} a1
 * @param {Array} a2
 * @return {Array}
 */
const arrayConcatUnique = function ( a1, a2 ) {
	return a1.concat( a2.filter( ( item ) => a1.indexOf( item ) === -1 ) );
};

module.exports = {
	arrayConcatUnique,
	arrayContains
};
