<?php

namespace Wikibase\Client\Hooks;

use ApiMain;
use ExtensionRegistry;
use Parser;
use Wikibase\Client\Api\ApiFormatReference;
use Wikibase\Client\DataAccess\ReferenceFormatterFactory;
use Wikibase\DataModel\Deserializers\DeserializerFactory;

/**
 * Do special hook registrations. These are affected by ordering issues and/or
 * conditional on another extension being registered.
 *
 * Strictly speaking, this isn’t a hook handler
 * (it’s called through $wgExtensionFunctions, not $wgHooks),
 * but it behaves very similarly so it’s grouped with the real hook handlers.
 *
 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:$wgExtensionFunctions
 * @license GPL-2.0-or-later
 */
class ExtensionLoadHandler {

	/** @var ExtensionRegistry */
	private $extensionRegistry;

	public function __construct(
		ExtensionRegistry $extensionRegistry
	) {
		$this->extensionRegistry = $extensionRegistry;
	}

	public static function factory(): self {
		return new self(
			ExtensionRegistry::getInstance()
		);
	}

	public static function onExtensionLoad() {
		global $wgHooks, $wgWBClientSettings, $wgAPIModules;

		$handler = self::factory();

		$wgHooks = array_merge_recursive( $wgHooks, $handler->getHooks() );

		if ( $wgWBClientSettings === null ) {
			$wgWBClientSettings = [];
		}

		$apiFormatReferenceSpec = $handler->getApiFormatReferenceSpec( $wgWBClientSettings );
		if ( $apiFormatReferenceSpec !== null ) {
			$wgAPIModules['wbformatreference'] = $apiFormatReferenceSpec;
		}
	}

	public function getHooks(): array {
		$hooks = [];

		// These hooks should only be run if we use the Echo extension
		if ( $this->extensionRegistry->isLoaded( 'Echo' ) ) {
			$hooks['LocalUserCreated'][] = EchoNotificationsHandlers::class . '::onLocalUserCreated';
			$hooks['WikibaseHandleChange'][] = EchoNotificationsHandlers::class . '::onWikibaseHandleChange';
		}

		// This is in onExtensionLoad to ensure we register our
		// ChangesListSpecialPageStructuredFilters after ORES's.
		//
		// However, ORES is not required.
		//
		// recent changes / watchlist hooks
		$hooks['ChangesListSpecialPageStructuredFilters'][] =
			ChangesListSpecialPageHookHandler::class . '::onChangesListSpecialPageStructuredFilters';

		return $hooks;
	}

	public function getApiFormatReferenceSpec( array $clientSettings ): ?array {
		// This API module is (for now) only enabled conditionally
		if ( !( $clientSettings['dataBridgeEnabled'] ?? false ) ) {
			return null;
		}

		return [
			'class' => ApiFormatReference::class,
			'services' => [
				'Parser',
				'WikibaseClient.BaseDataModelDeserializerFactory',
				'WikibaseClient.ReferenceFormatterFactory',
			],
			'factory' => function (
				ApiMain $apiMain,
				string $moduleName,
				Parser $parser,
				DeserializerFactory $deserializerFactory,
				ReferenceFormatterFactory $referenceFormatterFactory
			) {
				return new ApiFormatReference(
					$apiMain,
					$moduleName,
					$parser,
					$referenceFormatterFactory,
					$deserializerFactory->newReferenceDeserializer()
				);
			},
		];
	}

}
