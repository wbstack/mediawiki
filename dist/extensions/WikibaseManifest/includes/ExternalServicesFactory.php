<?php

namespace MediaWiki\Extension\WikibaseManifest;

interface ExternalServicesFactory {

	public function getExternalServices(): ExternalServices;

}
