<?php

namespace MediaWiki\Extension\WikibaseManifest;

interface EntityNamespacesFactory {

	public function getEntityNamespaces(): EntityNamespaces;

}
