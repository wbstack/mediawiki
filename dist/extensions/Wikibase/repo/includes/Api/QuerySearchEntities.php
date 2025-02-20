<?php

declare( strict_types = 1 );

namespace Wikibase\Repo\Api;

use MediaWiki\Api\ApiPageSet;
use MediaWiki\Api\ApiQuery;
use MediaWiki\Api\ApiQueryGeneratorBase;
use MediaWiki\Api\ApiUsageException;
use MediaWiki\Cache\LinkBatchFactory;
use Wikibase\Lib\ContentLanguages;
use Wikibase\Lib\Interactors\TermSearchResult;
use Wikibase\Lib\SettingsArray;
use Wikibase\Lib\Store\EntityTitleLookup;
use Wikimedia\Assert\InvariantException;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module to search for Wikibase entities that can be used as a generator.
 *
 * @license GPL-2.0-or-later
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QuerySearchEntities extends ApiQueryGeneratorBase {

	private LinkBatchFactory $linkBatchFactory;

	private EntitySearchHelper $entitySearchHelper;

	private EntityTitleLookup $titleLookup;

	private ContentLanguages $termsLanguages;

	/**
	 * @var string[] The supported entity types.
	 * This should be initialized from {@link WikibaseRepo::getEnabledEntityTypes()},
	 * <strong>not</strong> from {@link WikibaseRepo::getEnabledEntityTypesForSearch()} –
	 * unlike {@link SearchEntities}, this module does not support additional entity types
	 * that are not registered with Wikibase’s entity registration yet
	 * (every search result’s {@link TermSearchResult::getEntityId() entity ID}
	 * must be non-null so that we can use the {@link EntityTitleLookup}).
	 */
	private array $entityTypes;

	/** @var (string|null)[] */
	private array $searchProfiles;

	public function __construct(
		ApiQuery $apiQuery,
		string $moduleName,
		LinkBatchFactory $linkBatchFactory,
		EntitySearchHelper $entitySearchHelper,
		EntityTitleLookup $titleLookup,
		ContentLanguages $termsLanguages,
		array $entityTypes,
		array $searchProfiles
	) {
		parent::__construct( $apiQuery, $moduleName, 'wbs' );

		$this->linkBatchFactory = $linkBatchFactory;
		$this->entitySearchHelper = $entitySearchHelper;
		$this->titleLookup = $titleLookup;
		$this->termsLanguages = $termsLanguages;
		$this->entityTypes = $entityTypes;
		$this->searchProfiles = $searchProfiles;
	}

	public static function factory(
		ApiQuery $apiQuery,
		string $moduleName,
		LinkBatchFactory $linkBatchFactory,
		array $enabledEntityTypes,
		array $entitySearchHelperCallbacks,
		EntityTitleLookup $entityTitleLookup,
		SettingsArray $repoSettings,
		ContentLanguages $termsLanguages
	): self {
		return new self(
			$apiQuery,
			$moduleName,
			$linkBatchFactory,
			new TypeDispatchingEntitySearchHelper(
				$entitySearchHelperCallbacks,
				$apiQuery->getRequest()
			),
			$entityTitleLookup,
			$termsLanguages,
			$enabledEntityTypes,
			$repoSettings->getSetting( 'searchProfiles' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function execute(): void {
		$params = $this->extractRequestParams();
		$searchResults = $this->getSearchResults( $params );
		$result = $this->getResult();

		// prefetch page IDs
		$this->linkBatchFactory->newLinkBatch( array_map(
			fn ( TermSearchResult $match ) => $this->titleLookup->getTitleForId( $match->getEntityId() ),
			$searchResults
		) )->execute();

		foreach ( $searchResults as $match ) {
			$title = $this->titleLookup->getTitleForId( $match->getEntityId() );

			$values = [
				'ns' => $title->getNamespace(),
				'title' => $title->getPrefixedText(),
				'pageid' => intval( $title->getArticleID() ),
				'displaytext' => $match->getMatchedTerm()->getText(),
			];

			$result->addValue( [ 'query', $this->getModuleName() ], null, $values );
		}

		$result->addIndexedTagName(
			[ 'query', $this->getModuleName() ], $this->getModulePrefix()
		);
	}

	/**
	 * @param ApiPageSet $resultPageSet
	 */
	public function executeGenerator( $resultPageSet ): void {
		$params = $this->extractRequestParams();
		$searchResults = $this->getSearchResults( $params );
		$titles = [];

		foreach ( $searchResults as $match ) {
			$title = $this->titleLookup->getTitleForId( $match->getEntityId() );
			$titles[] = $title;
			$resultPageSet->setGeneratorData( $title, [ 'displaytext' => $match->getMatchedTerm()->getText() ] );
		}

		$resultPageSet->populateFromTitles( $titles );
	}

	/**
	 * @param array $params
	 *
	 * @return TermSearchResult[]
	 * @throws ApiUsageException
	 */
	private function getSearchResults( array $params ): array {
		try {
			return $this->entitySearchHelper->getRankedSearchResults(
				$params['search'],
				$params['language'] ?: $this->getLanguage()->getCode(),
				$params['type'],
				$params['limit'],
				$params['strictlanguage'],
				$this->searchProfiles[$params['profile']]
			);
		} catch ( EntitySearchException $ese ) {
			$this->dieStatus( $ese->getStatus() );

			// @phan-suppress-next-line PhanPluginUnreachableCode Wanted
			throw new InvariantException( "dieStatus() must throw an exception" );
		}
	}

	/**
	 * @see ApiQueryBase::getCacheMode
	 *
	 * @param array $params
	 * @return string
	 */
	public function getCacheMode( $params ): string {
		return 'public';
	}

	/**
	 * @inheritDoc
	 */
	public function isInternal(): bool {
		return true; // mark this api module as internal until we settled on a solution for search
	}

	/**
	 * @inheritDoc
	 */
	protected function getAllowedParams(): array {
		return [
			'search' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'language' => [
				ParamValidator::PARAM_TYPE => $this->termsLanguages->getLanguages(),
			],
			'strictlanguage' => [
				ParamValidator::PARAM_TYPE => 'boolean',
				ParamValidator::PARAM_DEFAULT => false,
			],
			'type' => [
				ParamValidator::PARAM_TYPE => $this->entityTypes,
				ParamValidator::PARAM_DEFAULT => 'item',
			],
			'limit' => [
				ParamValidator::PARAM_TYPE => 'limit',
				ParamValidator::PARAM_DEFAULT => 7,
				self::PARAM_MAX => self::LIMIT_SML1,
				self::PARAM_MAX2 => self::LIMIT_SML2,
				self::PARAM_MIN => 0,
			],
			'profile' => [
				ParamValidator::PARAM_TYPE => array_keys( $this->searchProfiles ),
				ParamValidator::PARAM_DEFAULT => array_key_first( $this->searchProfiles ),
				self::PARAM_HELP_MSG_PER_VALUE => [],
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages(): array {
		return [
			'action=query&list=wbsearch&wbssearch=abc&wbslanguage=en' => 'apihelp-query+wbsearch-example-1',
			'action=query&list=wbsearch&wbssearch=abc&wbslanguage=en&wbslimit=50' => 'apihelp-query+wbsearch-example-2',
			'action=query&list=wbsearch&wbssearch=alphabet&wbslanguage=en&wbstype=property' => 'apihelp-query+wbsearch-example-3',
			'action=query&generator=wbsearch&gwbssearch=alphabet&gwbslanguage=en' => 'apihelp-query+wbsearch-example-4',
		];
	}

}
