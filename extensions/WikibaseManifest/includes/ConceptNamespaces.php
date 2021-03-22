<?php

namespace MediaWiki\Extension\WikibaseManifest;

use Wikibase\Repo\Rdf\RdfVocabulary;

class ConceptNamespaces {
	private $localEntitySource;
	private $rdfVocabulary;

	public function __construct( $localEntitySource, RdfVocabulary $rdfVocabulary ) {
		$this->localEntitySource = $localEntitySource;
		$this->rdfVocabulary = $rdfVocabulary;
	}

	public function getLocal() {
		$rdfVocabulary = $this->rdfVocabulary;
		$dataNamespaces = [ RdfVocabulary::NS_DATA => $rdfVocabulary->dataNamespaceNames['local'] ];
		$entityNamespaces = [ RdfVocabulary::NS_ENTITY => $rdfVocabulary->entityNamespaceNames['local'] ];
		$propertyNamespaces = $rdfVocabulary->propertyNamespaceNames['local'];
		$statementNamespaces = $rdfVocabulary->statementNamespaceNames['local'];
		$relevant_ns = array_merge(
			$entityNamespaces,
			$dataNamespaces,
			$statementNamespaces,
			$propertyNamespaces
		);

		$result = [];
		foreach ( $relevant_ns as $key => $value ) {
			$result[$key] = $rdfVocabulary->getNamespaceURI( $value );
		}

		return $result;
	}
}
