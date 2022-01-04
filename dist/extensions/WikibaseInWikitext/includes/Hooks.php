<?php

namespace MediaWiki\Extension\WikibaseInWikitext;

use SpecialPage;
use Parser;
use PPFrame;

class Hooks {

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
	 * @param \Parser $parser
	 */
	public static function onParserFirstCallInit( \Parser $parser ) {
		$parser->setHook( 'sparql', [ self::class, 'renderTagSparql' ] );
	}

	// Render <sparql>
	public static function renderTagSparql( $input, array $args, Parser $parser, PPFrame $frame ) {
		global $wgWikibaseInWikitextSparqlDefaultUi;

		if ( array_key_exists( 'ui', $args ) ) {
			$sparqlUi = $args['ui'];
		} else {
			// TODO get from Wikibase config if exists?
			$sparqlUi = $wgWikibaseInWikitextSparqlDefaultUi;
		}
		$shouldList = array_key_exists( 'list', $args );

		$output = '';

		if ( $shouldList ) {
			$referencesEntities = [];
			foreach ( explode( PHP_EOL, $input ) as $line ) {
				if( strlen( $line ) === 0 || $line[0] === '#' ) {
					continue;
				}
				preg_match_all( '/([QP]\d+)/i', $line, $matches );
				$referencesEntities = array_merge( $referencesEntities, $matches[1] );
			}
			$referencesEntities = array_unique( $referencesEntities );
			sort( $referencesEntities );

			if( $referencesEntities ) {
				$output .= '<p>The following query uses these:</p>';
				$output .= '<ul>';
				foreach( $referencesEntities as $id ) {
					// TODO what if the entity is not on this local wiki?

					$output .= '<li><a href="' .
						SpecialPage::getTitleFor( 'EntityPage', $id )->getLinkURL() . '" >' .
						$id .
						'</li>';
				}
				$output .= '</ul>';
				$output .= PHP_EOL;
			}
		}

		if( \ExtensionRegistry::getInstance()->isLoaded( 'SyntaxHighlight' ) ) {
			$output .= $parser->recursiveTagParse( '<syntaxhighlight lang="sparql" >' . $input . '</syntaxhighlight>' );
		} else {
			$output .= '<pre>' . $input . '</pre>';
		}
		$output .= PHP_EOL;

		if ( array_key_exists( 'tryit', $args ) ) {
			$output .= '<a href="' . $sparqlUi . '#' . htmlentities( rawurlencode( trim( $input ) ) ) . '">Try it!</a>';
			$output .= PHP_EOL;
		}

		return $output;
	}

}
