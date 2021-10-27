<?php

namespace Wikibase\Repo\Maintenance;

use MediaWiki\MediaWikiServices;
use Serializers\Serializer;
use Wikibase\DataModel\Services\Entity\EntityPrefetcher;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;
use Wikibase\Lib\Store\EntityRevisionLookup;
use Wikibase\Repo\Dumpers\DumpGenerator;
use Wikibase\Repo\Dumpers\JsonDumpGenerator;
use Wikibase\Repo\Store\Sql\SqlEntityIdPagerFactory;
use Wikibase\Repo\WikibaseRepo;

require_once __DIR__ . '/DumpEntities.php';

/**
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 * @author Addshore
 */
class DumpJson extends DumpEntities {

	/**
	 * @var EntityRevisionLookup
	 */
	private $entityRevisionLookup;

	/**
	 * @var Serializer
	 */
	private $entitySerializer;

	/**
	 * @var EntityPrefetcher
	 */
	private $entityPrefetcher;

	/**
	 * @var PropertyDataTypeLookup
	 */
	private $propertyDatatypeLookup;

	/**
	 * @var bool
	 */
	private $hasHadServicesSet = false;

	public function __construct() {
		parent::__construct();

		$this->addOption(
			'snippet',
			'Output a JSON snippet without square brackets at the start and end. Allows output to'
				. ' be combined more freely.',
			false,
			false
		);
	}

	public function setServices(
		SqlEntityIdPagerFactory $sqlEntityIdPagerFactory,
		array $existingEntityTypes,
		EntityPrefetcher $entityPrefetcher,
		PropertyDataTypeLookup $propertyDataTypeLookup,
		EntityRevisionLookup $entityRevisionLookup,
		Serializer $entitySerializer
	) {
		parent::setDumpEntitiesServices(
			$sqlEntityIdPagerFactory,
			$existingEntityTypes,
			[]
		);
		$this->entityPrefetcher = $entityPrefetcher;
		$this->propertyDatatypeLookup = $propertyDataTypeLookup;
		$this->entityRevisionLookup = $entityRevisionLookup;
		$this->entitySerializer = $entitySerializer;
		$this->hasHadServicesSet = true;
	}

	public function execute() {
		if ( !$this->hasHadServicesSet ) {
			$wikibaseRepo = WikibaseRepo::getDefaultInstance();
			$mwServices = MediaWikiServices::getInstance();

			$sqlEntityIdPagerFactory = new SqlEntityIdPagerFactory(
				WikibaseRepo::getEntityNamespaceLookup( $mwServices ),
				WikibaseRepo::getEntityIdLookup( $mwServices ),
				$mwServices->getLinkCache()
			);
			$revisionLookup = $wikibaseRepo->getEntityRevisionLookup(
				$this->getEntityRevisionLookupCacheMode()
			);

			$this->setServices(
				$sqlEntityIdPagerFactory,
				$wikibaseRepo->getEnabledEntityTypes(),
				WikibaseRepo::getStore( $mwServices )->getEntityPrefetcher(),
				$wikibaseRepo->getPropertyDataTypeLookup(),
				$revisionLookup,
				WikibaseRepo::getCompactEntitySerializer( $mwServices )
			);
		}
		parent::execute();
	}

	/**
	 * Create concrete dumper instance
	 * @param resource $output
	 * @return DumpGenerator
	 */
	protected function createDumper( $output ) {
		$dataTypeLookup = $this->propertyDatatypeLookup;

		$dumper = new JsonDumpGenerator(
			$output,
			$this->entityRevisionLookup,
			$this->entitySerializer,
			$this->entityPrefetcher,
			$dataTypeLookup
		);

		$dumper->setUseSnippets( (bool)$this->getOption( 'snippet', false ) );
		return $dumper;
	}

	protected function getDumpType(): string {
		return "JSON";
	}
}

$maintClass = DumpJson::class;
require_once RUN_MAINTENANCE_IF_MAIN;
