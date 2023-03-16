<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Mailgun' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['Mailgun'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for Mailgun extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the Mailgun extension requires MediaWiki 1.34+' );
}
