/**
 * @param {string} num
 * @return {string}
 */
function lineFeeds( num ) {
	let out = '';
	num = parseInt( num, 10 );
	while ( num-- ) {
		out += '\n';
	}
	return out;
}

/**
 * @param {jQuery} $selected Columns to merge
 * @return {string}
 */
function merger( $selected ) {
	const textLines = [];

	$selected.each( ( index, element ) => {
		let line = $( element ).find( '.mw-twocolconflict-split-editor' ).val()
			.replace( /[\r\n]+$/, '' );
		let emptiedByUser = line === '';
		const $extraLineFeeds = $( element ).find( '[name^="mw-twocolconflict-split-linefeeds"]' );

		if ( $extraLineFeeds.length ) {
			const counts = $extraLineFeeds.val().split( ',', 2 );
			// "Before" and "after" are intentionally flipped, because "before" is very rare
			if ( 1 in counts ) {
				if ( counts[ 1 ] === 'was-empty' ) {
					emptiedByUser = false;
				} else {
					line = lineFeeds( counts[ 1 ] ) + line;
				}
			}
			line += lineFeeds( counts[ 0 ] );
		}

		if ( !emptiedByUser ) {
			textLines.push( line );
		}
	} );

	return textLines.join( '\n' );
}

module.exports = merger;
