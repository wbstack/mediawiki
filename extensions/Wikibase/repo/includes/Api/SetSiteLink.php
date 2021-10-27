<?php

declare( strict_types = 1 );

namespace Wikibase\Repo\Api;

use ApiMain;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\SiteLinkList;
use Wikibase\Lib\Summary;
use Wikibase\Repo\ChangeOp\ChangeOp;
use Wikibase\Repo\ChangeOp\ChangeOpException;
use Wikibase\Repo\ChangeOp\ChangeOpValidationException;
use Wikibase\Repo\ChangeOp\Deserialization\ChangeOpDeserializationException;
use Wikibase\Repo\ChangeOp\Deserialization\SiteLinkBadgeChangeOpSerializationValidator;
use Wikibase\Repo\ChangeOp\SiteLinkChangeOpFactory;
use Wikibase\Repo\WikibaseRepo;

/**
 * API module to associate a page on a site with a Wikibase entity or remove an already made such association.
 * Requires API write mode to be enabled.
 *
 * @license GPL-2.0-or-later
 */
class SetSiteLink extends ModifyEntity {

	/**
	 * @var SiteLinkChangeOpFactory
	 */
	private $siteLinkChangeOpFactory;

	/**
	 * @var SiteLinkBadgeChangeOpSerializationValidator
	 */
	private $badgeSerializationValidator;

	public function __construct(
		ApiMain $mainModule,
		string $moduleName,
		SiteLinkChangeOpFactory $siteLinkChangeOpFactory,
		SiteLinkBadgeChangeOpSerializationValidator $badgeSerializationValidator,
		bool $federatedPropertiesEnabled
	) {
		parent::__construct( $mainModule, $moduleName, $federatedPropertiesEnabled );

		$this->siteLinkChangeOpFactory = $siteLinkChangeOpFactory;
		$this->badgeSerializationValidator = $badgeSerializationValidator;
	}

	public static function factory(
		ApiMain $mainModule,
		string $moduleName,
		SiteLinkBadgeChangeOpSerializationValidator $siteLinkBadgeChangeOpSerializationValidator
	): self {
		$wikibaseRepo = WikibaseRepo::getDefaultInstance();

		return new self(
			$mainModule,
			$moduleName,
			$wikibaseRepo->getChangeOpFactoryProvider()
				->getSiteLinkChangeOpFactory(),
			$siteLinkBadgeChangeOpSerializationValidator,
			$wikibaseRepo->inFederatedPropertyMode()
		);
	}

	/**
	 * @see ApiBase::isWriteMode()
	 *
	 * @return bool Always true.
	 */
	public function isWriteMode(): bool {
		return true;
	}

	/**
	 * @see ApiBase::needsToken
	 *
	 * @return string
	 */
	public function needsToken(): string {
		return 'csrf';
	}

	/**
	 * Checks whether the link should be removed based on params
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	private function shouldRemove( array $params ): bool {
		if ( $params['linktitle'] === '' || ( !isset( $params['linktitle'] ) && !isset( $params['badges'] ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	protected function modifyEntity( EntityDocument $entity, ChangeOp $changeOp, array $preparedParameters ): Summary {
		if ( !( $entity instanceof Item ) ) {
			$this->errorReporter->dieError( "The given entity is not an item", "not-item" );
		}

		$item = $entity;
		$summary = $this->createSummary( $preparedParameters );
		$linksite = $this->stringNormalizer->trimToNFC( $preparedParameters['linksite'] );
		$hasLinkWithSiteId = $item->getSiteLinkList()->hasLinkWithSiteId( $linksite );
		$resultBuilder = $this->getResultBuilder();

		if ( $this->shouldRemove( $preparedParameters ) ) {
			if ( $hasLinkWithSiteId ) {
				$siteLink = $item->getSiteLinkList()->getBySiteId( $linksite );
				$this->applyChangeOp( $changeOp, $entity, $summary );
				$resultBuilder->addRemovedSiteLinks( new SiteLinkList( [ $siteLink ] ), 'entity' );
			}
		} else {
			try {
				$result = $changeOp->validate( $entity );
				if ( !$result->isValid() ) {
					throw new ChangeOpValidationException( $result );
				}

				$this->applyChangeOp( $changeOp, $entity, $summary );

				$link = $item->getSiteLinkList()->getBySiteId( $linksite );
				$resultBuilder->addSiteLinkList(
					new SiteLinkList( [ $link ] ),
					'entity',
					true // always add the URL
				);
			} catch ( ChangeOpException $ex ) {
				$this->errorReporter->dieException( $ex, 'modification-failed' );
			}
		}

		return $summary;
	}

	protected function getChangeOp( array $preparedParameters, EntityDocument $entity ): ChangeOp {
		if ( $this->shouldRemove( $preparedParameters ) ) {
			$linksite = $this->stringNormalizer->trimToNFC( $preparedParameters['linksite'] );
			return $this->siteLinkChangeOpFactory->newRemoveSiteLinkOp( $linksite );
		} else {
			$linksite = $this->stringNormalizer->trimToNFC( $preparedParameters['linksite'] );
			$sites = $this->siteLinkTargetProvider->getSiteList( $this->siteLinkGroups );
			$site = $sites->getSite( $linksite );

			if ( $site === false ) {
				$this->errorReporter->dieError(
					'The supplied site identifier was not recognized',
					'not-recognized-siteid'
				);
			}

			if ( isset( $preparedParameters['linktitle'] ) ) {
				$page = $site->normalizePageName( $this->stringNormalizer->trimWhitespace( $preparedParameters['linktitle'] ) );

				if ( $page === false ) {
					$this->errorReporter->dieWithError(
						[ 'wikibase-api-no-external-page', $linksite, $preparedParameters['linktitle'] ],
						'no-external-page'
					);
				}
			} else {
				$page = null;
			}

			$badges = ( isset( $preparedParameters['badges'] ) )
				? $this->parseSiteLinkBadges( $preparedParameters['badges'] )
				: null;

			return $this->siteLinkChangeOpFactory->newSetSiteLinkOp( $linksite, $page, $badges );
		}
	}

	private function parseSiteLinkBadges( array $badges ): array {
		try {
			$this->badgeSerializationValidator->validateBadgeSerialization( $badges );
		} catch ( ChangeOpDeserializationException $exception ) {
			$this->errorReporter->dieException( $exception, $exception->getErrorCode() );
		}

		return $this->getBadgeItemIds( $badges );
	}

	private function getBadgeItemIds( array $badges ): array {
		return array_map( function( $badge ) {
			return new ItemId( $badge );
		}, $badges );
	}

	/**
	 * @inheritDoc
	 */
	protected function getAllowedParams(): array {
		$sites = $this->siteLinkTargetProvider->getSiteList( $this->siteLinkGroups );

		return array_merge(
			parent::getAllowedParams(),
			[
				'linksite' => [
					self::PARAM_TYPE => $sites->getGlobalIdentifiers(),
					self::PARAM_REQUIRED => true,
				],
				'linktitle' => [
					self::PARAM_TYPE => 'string',
				],
				'badges' => [
					self::PARAM_TYPE => array_keys( $this->badgeItems ),
					self::PARAM_ISMULTI => true,
				],
			]
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages(): array {
		return [
			'action=wbsetsitelink&id=Q42&linksite=enwiki&linktitle=Hydrogen'
			=> 'apihelp-wbsetsitelink-example-1',
			'action=wbsetsitelink&id=Q42&linksite=enwiki&linktitle=Hydrogen&summary=Loves%20Oxygen'
			=> 'apihelp-wbsetsitelink-example-2',
			'action=wbsetsitelink&site=enwiki&title=Hydrogen&linksite=dewiki&linktitle=Wasserstoff'
			=> 'apihelp-wbsetsitelink-example-3',
			'action=wbsetsitelink&site=enwiki&title=Hydrogen&linksite=dewiki'
			=> 'apihelp-wbsetsitelink-example-4',
			'action=wbsetsitelink&site=enwiki&title=Hydrogen&linksite=plwiki&linktitle=Wodór&badges=Q149'
			=> 'apihelp-wbsetsitelink-example-5',
			'action=wbsetsitelink&id=Q42&linksite=plwiki&badges=Q2|Q149'
			=> 'apihelp-wbsetsitelink-example-6',
			'action=wbsetsitelink&id=Q42&linksite=plwiki&linktitle=Warszawa'
			=> 'apihelp-wbsetsitelink-example-7',
			'action=wbsetsitelink&id=Q42&linksite=plwiki&linktitle=Wodór&badges='
			=> 'apihelp-wbsetsitelink-example-8',
		];
	}

}
