<?php

namespace MediaWiki\Extension\WikibaseManifest;

class NullOAuthUrl implements OAuthUrl {

	public function getValue(): ?string {
		return null;
	}
}
