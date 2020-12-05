<?php

class ConfirmAccountHooks {

	/**
	 * This function is for setup that has to happen in Setup.php
	 * when the functions in $wgExtensionFunctions get executed.
	 * @return void
	 */
	public static function onExtensionFunctions() {
		global $wgEnableEmail, $wgConfirmAccountFSRepos, $wgUploadDirectory;

		# This extension needs email enabled!
		# Otherwise users can't get their passwords...
		if ( !$wgEnableEmail ) {
			echo "ConfirmAccount extension requires \$wgEnableEmail set to true.\n";
			exit( 1 );
		}

		if ( $wgConfirmAccountFSRepos['accountreqs']['directory'] === false ) {
			$wgConfirmAccountFSRepos['accountreqs']['directory'] =
				$wgUploadDirectory . '/accountreqs';
		}
		if ( $wgConfirmAccountFSRepos['accountcreds']['directory'] === false ) {
			$wgConfirmAccountFSRepos['accountcreds']['directory'] =
				$wgUploadDirectory . '/accountcreds';
		}
	}

}
