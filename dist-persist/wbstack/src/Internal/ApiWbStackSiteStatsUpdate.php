<?php

namespace WBStack\Internal;

/**
 * This should update the site_stats table
 */
class ApiWbStackSiteStatsUpdate extends \ApiBase {
    public function mustBePosted() {return true;}
    public function isWriteMode() {return true;}
    public function isInternal() {return true;}
    public function execute() {
        global $wgBaseDirectory;

        @set_time_limit( 60*5 ); // 5 mins maybe D:
		@ini_set( 'memory_limit', '-1' ); // also try to disable the memory limit? Is this even a good idea?
		
		$cmd = 'WBS_DOMAIN=' . $GLOBALS[WBSTACK_INFO_GLOBAL]->requestDomain . ' php ' . $wgBaseDirectory . '/maintenance/initSiteStats.php --update --active';
		exec($cmd, $out, $return);

		// Return appropriate result
		$res = [
			'script' => 'maintenance/initSiteStats.php',
			'return' => $return,
			'output' => $out,
		];
		$this->getResult()->addValue( null, $this->getModuleName(), $res );
    }

    public function getAllowedParams() {
        return [];
    }
}
