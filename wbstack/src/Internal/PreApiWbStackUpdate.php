<?php

namespace WBStack\Internal;

/**
 * This API can be used to shell out to update.php
 * 
 * WARNING: It has a timeout of 5 mins, so could fail?
 * TODO the timout should be passed by the caller? (and have a default max timeout of something big?)
 * TODO allow other maint scripts too?
 */

class PreApiWbStackUpdate {
    public function execute() {
        @set_time_limit( 60*5 ); // 5 mins maybe D:
		@ini_set( 'memory_limit', '-1' ); // also try to disable the memory limit? Is this even a good idea?

		// Run update.php
		$mwPath = realpath( __DIR__ . '/../../../' );
		$cmd = 'WBS_DOMAIN=' . $GLOBALS[WBSTACK_INFO_GLOBAL]->requestDomain . ' php ' . $mwPath . '/maintenance/update.php --quick';
		exec($cmd, $out, $return);

		// Return appropriate result
		$res = [
			'script' => 'maintenance/update.php',
			'return' => $return,
			'output' => $out,
		];
		echo json_encode(['wbstackUpdate' => $res]);
    }
}
