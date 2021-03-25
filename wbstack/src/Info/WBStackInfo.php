<?php

namespace WBStack\Info;

class WBStackInfo
{

    public $data;
    private $settingsIndex = [];
    public $requestDomain;

    /**
     * Construct directly from API response from API backend...
     * @param $apiResult
     * @return WBStackInfo|null
     */
    public static function newFromApiResult(
        /*object*/ $apiResult
    ) {
        $data = $apiResult->data;
        // TODO cleanup condition, as data will never be an array (as it is an object when true...)
        if ($data === null || $data === []) {
            return null;
        }
        return new self($data);
    }

    public static function setGlobalForRequestDomain( $requestDomain ) {
        $requestDomain = strtolower($requestDomain);

        if($requestDomain === 'maint') {
            self::setGlobalForGeneralMaintScript();
            return true;
        }

        $info = self::getData( $requestDomain );

        // Set the model to the globals to be used by local settings..
        /** @var WBStackInfo $info */
        $GLOBALS[WBSTACK_INFO_GLOBAL] = $info;

        // Let's assume success unless the "data" is null.
        return $info !== null;
    }

    private static function getData( $requestDomain ) {
        $info = self::getDataFromApcCache( $requestDomain );
        if( $info ) {
            return $info;
        }
        // TODO create an APC lock saying this proc is going to get fresh data?
        // TODO in reality all of this needs to change...
        $info = self::getDataFromApiRequest( $requestDomain );
        // Cache positive results for 10 seconds, negative for 2
        $ttl = $info ? 10 : 2;
        self::writeDataToApcCache( $requestDomain, $info, $ttl );
        return $info;
    }

    private static function getDataFromApcCache( $requestDomain ) {
        return apcu_fetch( self::getApcKey($requestDomain) );
    }

    private static function writeDataToApcCache( $requestDomain, ?WBStackInfo $info, $ttl ) {
        $result = apcu_store( self::getApcKey($requestDomain), $info, $ttl );
        if(!$result) {
            // TODO log?!
        }
    }

    private static function getApcKey( $requestDomain ) {
        return 'WBStackInfo.v1.requestDomain.' . $requestDomain;
    }

    /**
     * @param $requestDomain
     * @return WBStackInfo|null
     */
    private static function getDataFromApiRequest( $requestDomain ) {
        // START generic getting of wiki info from domain
        $url = 'http://' . getenv( 'PLATFORM_API_BACKEND_HOST' ) . '/backend/wiki/getWikiForDomain?domain=' . urlencode($requestDomain);
        $headers = [
            'X-Backend-Service: backend-service',
            'X-Backend-Token: backend-token',
        ];

        $client = curl_init($url);
        curl_setopt($client, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $client, CURLOPT_USERAGENT, "WBStack - MediaWiki - WBStackInfo::getDataFromApiRequest" );

        $response = curl_exec($client);

        // TODO detect non 200 response here, and pass that out to the user as an error

        $info = WBStackInfo::newFromApiResult(json_decode($response));
        if (!$info) {
            return null;
        }
        $info->requestDomain = $requestDomain;
        return $info;
    }

    private function setGlobalForGeneralMaintScript() {
        $info = WBStackInfo::newFromApiResult(json_decode(file_get_contents(__DIR__ . '/../../data/WikiInfo-maint.json')));

        $info->requestDomain = 'maintenance';

        // Set the model to the globals to be used by local settings..
        /** @var WBStackInfo $info */
        $GLOBALS[WBSTACK_INFO_GLOBAL] = $info;
    }

    public function __construct($data)
    {
        // Create settings index
        foreach ($data->settings as $setting) {
            $this->settingsIndex[$setting->name] = $setting->value;
        }
        // Remove settings from general data
        unset($data->settings);
        // Make the rest of the data accessible
        $this->data = $data;
    }

    // Get a setting by name, null if not set..
    // (allows defaults to be in LocalSettings?
    public function getSetting($name)
    {
        if (array_key_exists($name, $this->settingsIndex)) {
            return $this->settingsIndex[$name];
        }
        return null;
    }

    public function __get($name)
    {
        if (property_exists($this->data, $name)) {
            return $this->data->$name;
        }
    }

}
