<?php

namespace WBStack\Internal;

use Wikimedia\ParamValidator\ParamValidator;

/**
 * This API is used by tools that need OAuth consumers.
 * Calling this API will either give you details for the spec that you ask if they already exist.
 * OR it will create such a consume, and give you the details.
 * 
 * Most of the logic for OAuth stuff currently lives within WbStackPlatformReservedUser
 */

class ApiWbStackOauthGet extends \ApiBase {
    public function mustBePosted() {return true;}
    public function isWriteMode() {return true;}
    public function isInternal() {return true;}

    private function getScheme(): String {
        $isLocal = preg_match("/(\w\.localhost)/", $GLOBALS[WBSTACK_INFO_GLOBAL]->requestDomain) === 1;
        return $isLocal ? 'http://' : 'https://';
    }

    public function execute() {
        // Try and get the required consumer
        $consumerData = WbStackPlatformReservedUser::getOAuthConsumer(
            $this->getParameter('consumerName'),
            $this->getParameter('consumerVersion')
        );

        // If it doesnt exist, make sure the user and consumer do
        if(!$consumerData) {
            $callbackUrl = $this->getScheme() . $GLOBALS[WBSTACK_INFO_GLOBAL]->requestDomain . $this->getParameter('callbackUrlTail');

            WbStackPlatformReservedUser::createIfNotExists();
            WbStackPlatformReservedUser::createOauthConsumer(
                $this->getParameter('consumerName'),
                $this->getParameter('consumerVersion'),
                $this->getParameter('grants'),
                $callbackUrl
            );
            $consumerData = WbStackPlatformReservedUser::getOAuthConsumer(
                $this->getParameter('consumerName'),
                $this->getParameter('consumerVersion')
            );
        }

        // Return appropriate result
        if(!$consumerData) {
            $res = ['success' => 0];
        } else {
            $res = [
                'success' => '1',
                'data' => $consumerData,
            ];
        }

        $this->getResult()->addValue( null, $this->getModuleName(), $res );

    }
    public function getAllowedParams() {
        return [
            'consumerName' => [
                ParamValidator::PARAM_TYPE => 'string',
                ParamValidator::PARAM_REQUIRED => true
            ],
            'consumerVersion' => [
                ParamValidator::PARAM_TYPE => 'string',
                ParamValidator::PARAM_REQUIRED => true
            ],
            'grants' => [
                ParamValidator::PARAM_TYPE => 'string',
                ParamValidator::PARAM_ISMULTI => true,
            ],
            'callbackUrlTail' => [
                ParamValidator::PARAM_TYPE => 'string',
                ParamValidator::PARAM_REQUIRED => true
            ],
        ];
    }
}
