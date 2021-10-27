<?php

namespace Wikibase\Lexeme\MediaWiki\Api;

use ApiBase;
use ApiMain;
use LogicException;
use Message;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\Lexeme\Domain\Model\Lexeme;
use Wikibase\Lexeme\MediaWiki\Api\Error\LexemeNotFound;
use Wikibase\Lexeme\MediaWiki\Api\Error\SenseNotFound;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\SenseIdDeserializer;
use Wikibase\Lib\Store\EntityRevision;
use Wikibase\Lib\Store\EntityRevisionLookup;
use Wikibase\Lib\Store\LookupConstants;
use Wikibase\Lib\Store\StorageException;
use Wikibase\Lib\Summary;
use Wikibase\Repo\Api\ApiErrorReporter;
use Wikibase\Repo\ChangeOp\ChangeOpValidationException;
use Wikibase\Repo\EditEntity\MediawikiEditEntityFactory;
use Wikibase\Repo\Store\Store;
use Wikibase\Repo\SummaryFormatter;
use Wikibase\Repo\WikibaseRepo;

/**
 * @license GPL-2.0-or-later
 */
class RemoveSense extends ApiBase {

	private const LATEST_REVISION = 0;

	/**
	 * @var RemoveSenseRequestParser
	 */
	private $requestParser;

	/**
	 * @var ApiErrorReporter
	 */
	private $errorReporter;

	/**
	 * @var MediawikiEditEntityFactory
	 */
	private $editEntityFactory;

	/**
	 * @var SummaryFormatter
	 */
	private $summaryFormatter;

	/**
	 * @var EntityRevisionLookup
	 */
	private $entityRevisionLookup;

	public static function factory(
		ApiMain $mainModule,
		string $moduleName,
		EntityIdParser $entityIdParser
	): self {
		$wikibaseRepo = WikibaseRepo::getDefaultInstance();
		$apiHelperFactory = $wikibaseRepo->getApiHelperFactory( $mainModule->getContext() );

		return new self(
			$mainModule,
			$moduleName,
			new RemoveSenseRequestParser(
				new SenseIdDeserializer( $entityIdParser )
			),
			$wikibaseRepo->getEntityRevisionLookup( Store::LOOKUP_CACHING_DISABLED ),
			$wikibaseRepo->newEditEntityFactory( $mainModule->getContext() ),
			$wikibaseRepo->getSummaryFormatter(),
			function ( $module ) use ( $apiHelperFactory ) {
				return $apiHelperFactory->getErrorReporter( $module );
			}
		);
	}

	public function __construct(
		ApiMain $mainModule,
		$moduleName,
		RemoveSenseRequestParser $requestParser,
		EntityRevisionLookup $entityRevisionLookup,
		MediawikiEditEntityFactory $editEntityFactory,
		SummaryFormatter $summaryFormatter,
		callable $errorReporterInstantiator
	) {
		parent::__construct( $mainModule, $moduleName );

		$this->errorReporter = $errorReporterInstantiator( $this );
		$this->requestParser = $requestParser;
		$this->editEntityFactory = $editEntityFactory;
		$this->entityRevisionLookup = $entityRevisionLookup;
		$this->summaryFormatter = $summaryFormatter;
	}

	/**
	 * @see ApiBase::execute()
	 *
	 * @throws \ApiUsageException
	 */
	public function execute() {
		$params = $this->extractRequestParams();
		$request = $this->requestParser->parse( $params );
		if ( $request->getBaseRevId() ) {
			$baseRevId = $request->getBaseRevId();
		} else {
			$baseRevId = self::LATEST_REVISION;
		}

		try {
			$senseId = $request->getSenseId();
			$lexemeId = $senseId->getLexemeId();

			$lexemeRevision = $this->entityRevisionLookup->getEntityRevision(
				$lexemeId,
				$baseRevId,
				LookupConstants::LATEST_FROM_MASTER
			);

			if ( !$lexemeRevision ) {
				$error = new LexemeNotFound( $lexemeId );
				$this->dieWithError( $error->asApiMessage( RemoveSenseRequestParser::PARAM_SENSE_ID, [] ) );
			}

			$baseRevId = $lexemeRevision->getRevisionId();
			/** @var Lexeme $lexeme */
			$lexeme = $lexemeRevision->getEntity();
			'@phan-var Lexeme $lexeme';

			if ( $lexeme->getSenses()->getById( $senseId ) === null ) {
				$error = new SenseNotFound( $senseId );
				$this->dieWithError( $error->asApiMessage( RemoveSenseRequestParser::PARAM_SENSE_ID, [] ) );
			}
		} catch ( StorageException $e ) {
			// TODO Test it
			if ( $e->getStatus() ) {
				$this->dieStatus( $e->getStatus() );
			} else {
				throw new LogicException(
					'StorageException caught with no status',
					0,
					$e
				);
			}
		}

		$summary = new Summary();
		$changeOp = $request->getChangeOp();

		$result = $changeOp->validate( $lexeme );
		if ( !$result->isValid() ) {
			$this->errorReporter->dieException(
				new ChangeOpValidationException( $result ),
				'modification-failed'
			);
		}

		$changeOp->apply( $lexeme, $summary );

		$editEntity = $this->editEntityFactory->newEditEntity(
			$this->getUser(),
			$lexemeId,
			$baseRevId
		);
		$flags = EDIT_UPDATE;
		if ( isset( $params['bot'] ) && $params['bot'] &&
			$this->getPermissionManager()->userHasRight( $this->getUser(), 'bot' )
		) {
			$flags |= EDIT_FORCE_BOT;
		}

		$tokenThatDoesNotNeedChecking = false;
		// FIXME: Handle failure
		$status = $editEntity->attemptSave(
			$lexeme,
			$this->summaryFormatter->formatSummary( $summary ),
			$flags,
			$tokenThatDoesNotNeedChecking
		);

		if ( !$status->isOK() ) {
			$this->dieStatus( $status );
		}

		/** @var EntityRevision $entityRevision */
		$entityRevision = $status->getValue()['revision'];

		$apiResult = $this->getResult();
		$apiResult->addValue( null, 'lastrevid', $entityRevision->getRevisionId() );
		$apiResult->addValue( null, 'success', 1 );
	}

	/**
	 * @see ApiBase::getAllowedParams
	 */
	protected function getAllowedParams() {
		// TODO baserevid (not in addsense etc currently....)
		return array_merge(
			[
				RemoveSenseRequestParser::PARAM_SENSE_ID => [
					self::PARAM_TYPE => 'string',
					self::PARAM_REQUIRED => true,
				],
				AddFormRequestParser::PARAM_BASEREVID => [
					self::PARAM_TYPE => 'integer',
				],
				'bot' => [
					self::PARAM_TYPE => 'boolean',
					self::PARAM_DFLT => false,
				],
			]
		);
	}

	/**
	 * @see ApiBase::isWriteMode()
	 */
	public function isWriteMode() {
		return true;
	}

	/**
	 * As long as this codebase is in development and APIs might change any time without notice, we
	 * mark all as internal. This adds an "unstable" notice, but does not hide them in any way.
	 */
	public function isInternal() {
		return true;
	}

	/**
	 * @see ApiBase::needsToken()
	 */
	public function needsToken() {
		return 'csrf';
	}

	/**
	 * @see ApiBase::mustBePosted()
	 */
	public function mustBePosted() {
		return true;
	}

	protected function getExamplesMessages() {
		$senseId = 'L10-S20';

		$query = http_build_query( [
			'action' => $this->getModuleName(),
			RemoveSenseRequestParser::PARAM_SENSE_ID => $senseId
		] );

		$exampleMessage = new Message(
			'apihelp-wblremovesense-example-1',
			[ $senseId ]
		);

		return [
			urldecode( $query ) => $exampleMessage
		];
	}

}
