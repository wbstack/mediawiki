<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'InviteSignup' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['InviteSignup'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['InviteSignupAlias'] = __DIR__ . '/InviteSignup.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for InviteSignup extension. ' .
		'Please use wfLoadExtension instead, see ' .
		'https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);

	return;
} else {
	die( 'This version of the InviteSignup extension requires MediaWiki 1.25+' );
}
