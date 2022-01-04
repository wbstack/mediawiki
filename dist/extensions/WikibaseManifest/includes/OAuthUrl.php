<?php

namespace MediaWiki\Extension\WikibaseManifest;

interface OAuthUrl {
	public function getValue(): ?string;
}
