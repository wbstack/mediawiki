<?php

use EchoPush\NotificationServiceClient;
use EchoPush\SubscriptionManager;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Storage\NameTableStore;

return [

	'EchoAttributeManager' => static function ( MediaWikiServices $services ): EchoAttributeManager {
		$userGroupManager = $services->getUserGroupManager();
		$echoConfig = $services->getConfigFactory()->makeConfig( 'Echo' );
		$notifications = $echoConfig->get( 'EchoNotifications' );
		$categories = $echoConfig->get( 'EchoNotificationCategories' );
		$typeAvailability = $echoConfig->get( 'DefaultNotifyTypeAvailability' );
		$typeAvailabilityByCategory = $echoConfig->get( 'NotifyTypeAvailabilityByCategory' );

		return new EchoAttributeManager(
			$notifications,
			$categories,
			$typeAvailability,
			$typeAvailabilityByCategory,
			$userGroupManager,
			$services->getUserOptionsLookup()
		);
	},

	'EchoPushNotificationServiceClient' => static function (
		MediaWikiServices $services
	): NotificationServiceClient {
		$echoConfig = $services->getConfigFactory()->makeConfig( 'Echo' );
		$httpRequestFactory = $services->getHttpRequestFactory();
		$url = $echoConfig->get( 'EchoPushServiceBaseUrl' );
		$client = new NotificationServiceClient( $httpRequestFactory, $url );
		$client->setLogger( LoggerFactory::getInstance( 'Echo' ) );
		return $client;
	},

	'EchoPushSubscriptionManager' => static function ( MediaWikiServices $services ): SubscriptionManager {
		$echoConfig = $services->getConfigFactory()->makeConfig( 'Echo' );
		// Use shared DB/cluster for push subscriptions
		$cluster = $echoConfig->get( 'EchoSharedTrackingCluster' );
		$database = $echoConfig->get( 'EchoSharedTrackingDB' );
		$loadBalancerFactory = $services->getDBLoadBalancerFactory();
		$loadBalancer = $cluster
			? $loadBalancerFactory->getExternalLB( $cluster )
			: $loadBalancerFactory->getMainLB( $database );
		$dbw = $loadBalancer->getLazyConnectionRef( DB_PRIMARY, [], $database );
		$dbr = $loadBalancer->getLazyConnectionRef( DB_REPLICA, [], $database );

		$pushProviderStore = new NameTableStore(
			$loadBalancer,
			$services->getMainWANObjectCache(),
			LoggerFactory::getInstance( 'Echo' ),
			'echo_push_provider',
			'epp_id',
			'epp_name',
			null,
			$database
		);

		$pushTopicStore = new NameTableStore(
			$loadBalancer,
			$services->getMainWANObjectCache(),
			LoggerFactory::getInstance( 'Echo' ),
			'echo_push_topic',
			'ept_id',
			'ept_text',
			null,
			$database
		);

		$maxSubscriptionsPerUser = $echoConfig->get( 'EchoPushMaxSubscriptionsPerUser' );

		return new SubscriptionManager(
			$dbw,
			$dbr,
			$pushProviderStore,
			$pushTopicStore,
			$maxSubscriptionsPerUser
		);
	}

];