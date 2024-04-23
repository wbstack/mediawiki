<?php

namespace WBStack\Internal;

use Wikimedia\ParamValidator\ParamValidator;

/**
 * This should create the index for the requested wiki
 */
class ApiWbStackElasticSearchInit extends \ApiBase {
    public function mustBePosted() {return true;}
    public function isWriteMode() {return true;}
    public function isInternal() {return true;}
    public function execute() {
        global $wgBaseDirectory;

        @set_time_limit( 60*5 ); // 5 mins maybe D:
		@ini_set( 'memory_limit', '-1' ); // also try to disable the memory limit? Is this even a good idea?

		$cluster = $this->getParameter( 'cluster' );
		$parameters = '--cluster ' . escapeshellarg( $cluster );
		$cmd = 'WBS_DOMAIN=' . $GLOBALS[WBSTACK_INFO_GLOBAL]->requestDomain . ' php ' . $wgBaseDirectory . '/extensions/CirrusSearch/maintenance/UpdateSearchIndexConfig.php ' . $parameters;
		exec($cmd, $out, $return);

		// Return appropriate result
		$res = [
			'script' => 'extensions/CirrusSearch/maintenance/UpdateSearchIndexConfig.php',
			'return' => $return,
			'output' => $out,
		];
		$this->getResult()->addValue( null, $this->getModuleName(), $res );
    }

    public function getAllowedParams() {
        return [
            'cluster' => [
                // Value can be 'all' or a CirrusSearch cluster name defined in LocalSettings.php
                ParamValidator::PARAM_TYPE => 'string',
                ParamValidator::PARAM_REQUIRED => true,
                ParamValidator::PARAM_DEFAULT => 'all'
            ]
        ];
    }
}
