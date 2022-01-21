<?php

namespace WBStack\Internal;

/**
 * This should populate the ElasticSearch index for the requested wiki
 */
class ApiWbStackForceSearchIndex extends \ApiBase {
    public function mustBePosted() {return true;}
    public function isWriteMode() {return true;}
    public function isInternal() {return true;}
    public function execute() {
        global $IP;

        @set_time_limit( 60*5 ); // 5 mins maybe D:
		@ini_set( 'memory_limit', '-1' ); // also try to disable the memory limit? Is this even a good idea?
		
        $fromId = $this->getParameter('fromId');
        $toId = $this->getParameter('toId');

        $parameters = "--skipLinks 1 --indexOnSkip 1 --fromId ${fromId}  --toId ${toId}";
		$cmd = 'WBS_DOMAIN=' . $GLOBALS[WBSTACK_INFO_GLOBAL]->requestDomain . ' php ' . $IP . '/extensions/CirrusSearch/maintenance/ForceSearchIndex.php ' . $parameters;
		exec($cmd, $out, $return);

		// Return appropriate result
		$res = [
			'script' => 'extensions/CirrusSearch/maintenance/ForceSearchIndex.php',
			'return' => $return,
			'output' => $out,
		];
		$this->getResult()->addValue( null, $this->getModuleName(), $res );
    }

    public function getAllowedParams() {
        return [
            'fromId' => [
                \ApiBase::PARAM_TYPE => 'integer',
                \ApiBase::PARAM_REQUIRED => true
            ],
            'toId' => [
                \ApiBase::PARAM_TYPE => 'integer',
                \ApiBase::PARAM_REQUIRED => true
            ],
        ];
    }
}
