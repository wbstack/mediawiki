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
        @set_time_limit( 60*60 ); // 60 mins maybe D:
		@ini_set( 'memory_limit', '-1' ); // also try to disable the memory limit? Is this even a good idea?

		// Run update.php
		$domain = $GLOBALS[WBSTACK_INFO_GLOBAL]->requestDomain;
		$mwPath = realpath( __DIR__ . '/../../../' );
		$cmd = 'WBS_DOMAIN=' . $domain . ' php ' . $mwPath . '/maintenance/update.php --quick';

		$stdout = fopen( 'php://stdout', 'w' );

		fwrite( $stdout, "DOMAIN: " . $domain . "\n" );

		$spec = [ [ 'pipe', 'r' ], [ 'pipe', 'w' ],[ 'pipe', 'w' ] ];
		$proc = proc_open( $cmd, $spec, $pipes );

		$stdinProc = $pipes[0];
		$stdoutProc = $pipes[1];
		$stderrProc = $pipes[2];

		$out = [];

		$pid = ( proc_get_status( $proc ) )[ 'pid' ];
		fwrite( $stdout, "PID: " . $pid . "\n" );

		while( $line = fgets( $stdoutProc ) ) {
			$line = rtrim( $line );
			fwrite( $stdout, $line . PHP_EOL );
			array_push( $out, $line );
		}

		while( $line = fgets( $stderrProc ) ) {
			$line = rtrim( $line );
			fwrite( $stdout, $line . PHP_EOL ); // effectively redirecting stdErr to stdOut
			array_push( $out, $line );
		}

		fclose( $stdinProc );
		fclose( $stdoutProc );
		fclose( $stderrProc );

		$return = ( proc_get_status( $proc ) )[ 'exitcode' ];
		proc_close( $proc );

		// Return appropriate result
		$res = [
			'script' => 'maintenance/update.php',
			'return' => $return,
			'output' => $out,
		];
		echo json_encode(['wbstackUpdate' => $res]);
    }
}
