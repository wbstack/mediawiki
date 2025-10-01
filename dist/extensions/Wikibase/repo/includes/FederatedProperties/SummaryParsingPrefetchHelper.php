<?php
declare( strict_types = 1 );

namespace Wikibase\Repo\FederatedProperties;

use LogicException;
use MediaWiki\Revision\RevisionRecord;
use Psr\Log\LoggerInterface;
use Wikibase\DataAccess\PrefetchingTermLookup;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikimedia\Rdbms\IResultWrapper;

/**
 * A helper class for parsing and prefetching properties from summaries for federated properties
 *
 * @license GPL-2.0-or-later
 */
class SummaryParsingPrefetchHelper {

	/**
	 * @var PrefetchingTermLookup
	 */
	private $prefetchingLookup;

	/** @var LoggerInterface */
	private $logger;

	public function __construct(
		PrefetchingTermLookup $prefetchingLookup,
		LoggerInterface $logger

	) {
		$this->prefetchingLookup = $prefetchingLookup;
		$this->logger = $logger;
	}

	/**
	 * Matching links to properties in in edit summaries, such as "[[Property:P123]]" or "[[wdbeta:Special:EntityPage/P123]]"
	 */
	public const PROPERTY_SUMMARY_REGEXP = '/\[\[(\S+)(P[1-9]\d*)\]\]/';

	/**
	 * @param IResultWrapper|\stdClass[]|RevisionRecord[] $rows
	 * @param array $languageCodes
	 * @param array $termTypes
	 */
	public function prefetchFederatedProperties( $rows, array $languageCodes, array $termTypes ): void {
		$resultProperties = $this->extractSummaryProperties( $rows );
		if ( !$resultProperties ) {
			return;
		}
		try {
			$this->prefetchingLookup->prefetchTerms(
				$resultProperties,
				$termTypes,
				$languageCodes
			);
		} catch ( FederatedPropertiesException $ex ) {
			$this->logger->warning( 'Prefetching failed for federated properties: {resultProperties}', [
				'resultProperties' => implode( ',', $resultProperties ),
				'exception' => $ex,
			] );
		}
	}

	/**
	 * @param IResultWrapper|\stdClass[]|RevisionRecord[] $result
	 * @return PropertyId[]
	 */
	public function extractSummaryProperties( $result ): array {
		$propertyIds = [];
		foreach ( $result as $revisionRow ) {
			$comment = $this->getCommentText( $revisionRow );
			if ( $comment === null ) {
				continue;
			}

			$matches = [];
			preg_match( self::PROPERTY_SUMMARY_REGEXP, $comment, $matches );
			if ( count( $matches ) === 3 ) {
				$propertyId = $matches[2];
				// TODO: Change to FederatedPropertyId when this functionality is supported in Feddy Props v2
				$propertyIds[] = new NumericPropertyId( $propertyId );
			}
		}
		return $propertyIds;
	}

	/**
	 * @param \stdClass|RevisionRecord|null $revisionRow
	 * @return string|null
	 */
	private function getCommentText( $revisionRow ) {
		if ( $revisionRow === null ) {
			return null;
		}

		if ( $revisionRow instanceof RevisionRecord ) {
			$comment = $revisionRow->getComment();
			return $comment === null ? null : $comment->text;
		}

		if ( property_exists( $revisionRow, 'rc_comment_text' ) ) {
			return $revisionRow->rc_comment_text;
		} elseif ( property_exists( $revisionRow, 'rev_comment_text' ) ) {
			return $revisionRow->rev_comment_text;
		}

		throw new LogicException( 'Rows should have either rc_comment_text or rev_comment_text field' );
	}
}
