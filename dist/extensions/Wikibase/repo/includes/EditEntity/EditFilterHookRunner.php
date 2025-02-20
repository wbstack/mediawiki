<?php

namespace Wikibase\Repo\EditEntity;

use InvalidArgumentException;
use MediaWiki\Context\IContextSource;
use MediaWiki\Status\Status;
use RuntimeException;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\EntityRedirect;
use Wikibase\Repo\Content\EntityContent;

/**
 * Interface to run a hook before and edit is saved.
 *
 * @license GPL-2.0-or-later
 * @author Addshore
 */
interface EditFilterHookRunner {

	/**
	 * Call EditFilterMergedContent hook, if registered.
	 *
	 * @param EntityDocument|EntityRedirect|EntityContent|null $new The entity or redirect (content) we are trying to save
	 * @param IContextSource $context The request context for the edit
	 * @param string $summary The edit summary
	 *
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 * @return Status
	 */
	public function run( $new, IContextSource $context, string $summary );

}
