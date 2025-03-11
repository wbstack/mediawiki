<?php
/**
 * The web entry point to be used as 404 handler behind a web server rewrite
 * rule for media thumbnails, internally handled via thumb.php.
 *
 * This script will interpret a request URL like
 * `/w/images/thumb/a/a9/Example.jpg/50px-Example.jpg` and treat it as
 * if it was a request to thumb.php with the relevant query parameters filled
 * out. See also $wgGenerateThumbnailOnParse.
 *
 * @see thumb.php
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup entrypoint
 * @ingroup Media
 */

use MediaWiki\Context\RequestContext;
use MediaWiki\EntryPointEnvironment;
use MediaWiki\FileRepo\Thumbnail404EntryPoint;
use MediaWiki\MediaWikiServices;

define( 'MW_NO_OUTPUT_COMPRESSION', 1 );
define( 'MW_ENTRY_POINT', 'thumb_handler' );

require __DIR__ . '/includes/WebStart.php';

( new Thumbnail404EntryPoint(
	RequestContext::getMain(),
	new EntryPointEnvironment(),
	MediaWikiServices::getInstance()
) )->run();
