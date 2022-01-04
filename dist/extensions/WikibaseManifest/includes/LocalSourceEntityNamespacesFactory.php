<?php

namespace MediaWiki\Extension\WikibaseManifest;

use NamespaceInfo;
use Wikibase\DataAccess\EntitySource;

class LocalSourceEntityNamespacesFactory implements EntityNamespacesFactory {
	private $localEntitySource;
	private $namespaceInfo;

	public function __construct( EntitySource $localEntitySource, NamespaceInfo $namespaceInfo ) {
		$this->localEntitySource = $localEntitySource;
		$this->namespaceInfo = $namespaceInfo;
	}

	public function getEntityNamespaces(): EntityNamespaces {
		$entityNamespaceMapping = array_map(
			function ( $x ) {
				return [
					EntityNamespaces::NAMESPACE_ID => $x,
					EntityNamespaces::NAMESPACE_NAME => $this->namespaceInfo->getCanonicalName(
						$x )
				];
			},
			$this->localEntitySource->getEntityNamespaceIds()
		);

		return new EntityNamespaces( $entityNamespaceMapping );
	}
}
