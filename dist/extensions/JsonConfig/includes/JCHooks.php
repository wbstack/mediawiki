<?php

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace JsonConfig;

use MediaWiki\Api\ApiModuleManager;
use MediaWiki\Api\Hook\ApiMain__moduleManagerHook;
use MediaWiki\Config\Config;
use MediaWiki\Content\Content;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Content\Hook\ContentHandlerForModelIDHook;
use MediaWiki\Content\Hook\GetContentModelsHook;
use MediaWiki\Content\IContentHandlerFactory;
use MediaWiki\Context\IContextSource;
use MediaWiki\EditPage\EditPage;
use MediaWiki\Hook\AlternateEditHook;
use MediaWiki\Hook\CanonicalNamespacesHook;
use MediaWiki\Hook\EditFilterMergedContentHook;
use MediaWiki\Hook\EditPage__showEditForm_initialHook;
use MediaWiki\Hook\EditPageCopyrightWarningHook;
use MediaWiki\Hook\MovePageIsValidMoveHook;
use MediaWiki\Hook\PageMoveCompleteHook;
use MediaWiki\Hook\SkinCopyrightFooterMessageHook;
use MediaWiki\Hook\TitleGetEditNoticesHook;
use MediaWiki\Html\Html;
use MediaWiki\Message\Message;
use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\Hook\ArticleDeleteCompleteHook;
use MediaWiki\Page\Hook\ArticleUndeleteHook;
use MediaWiki\Permissions\Hook\GetUserPermissionsErrorsHook;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Revision\Hook\ContentHandlerDefaultModelForHook;
use MediaWiki\Status\Status;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Wikimedia\Message\MessageSpecifier;

/**
 * Hook handlers for JsonConfig extension.
 *
 * @file
 * @ingroup Extensions
 * @ingroup JsonConfig
 * @license GPL-2.0-or-later
 */
class JCHooks implements
	ApiMain__moduleManagerHook,
	ArticleDeleteCompleteHook,
	ArticleUndeleteHook,
	BeforePageDisplayHook,
	CanonicalNamespacesHook,
	ContentHandlerDefaultModelForHook,
	ContentHandlerForModelIDHook,
	GetContentModelsHook,
	AlternateEditHook,
	EditPage__showEditForm_initialHook,
	EditFilterMergedContentHook,
	EditPageCopyrightWarningHook,
	MovePageIsValidMoveHook,
	PageSaveCompleteHook,
	SkinCopyrightFooterMessageHook,
	TitleGetEditNoticesHook,
	PageMoveCompleteHook,
	GetUserPermissionsErrorsHook
{
	private Config $config;
	private IContentHandlerFactory $contentHandlerFactory;

	public function __construct(
		Config $config,
		IContentHandlerFactory $contentHandlerFactory
	) {
		$this->config = $config;
		$this->contentHandlerFactory = $contentHandlerFactory;
	}

	/**
	 * Only register NS_CONFIG if running on the MediaWiki instance which houses
	 * the JSON configs (i.e. META)
	 * @param array &$namespaces
	 */
	public function onCanonicalNamespaces( &$namespaces ) {
		if ( !self::jsonConfigIsStorage( $this->config ) ) {
			return;
		}

		JCSingleton::init();
		foreach ( JCSingleton::$namespaces as $ns => $name ) {
			if ( $name === false ) { // must be already declared
				if ( !array_key_exists( $ns, $namespaces ) ) {
					wfLogWarning( "JsonConfig: Invalid \$wgJsonConfigs: Namespace $ns " .
						"has not been declared by core or other extensions" );
				}
			} elseif ( array_key_exists( $ns, $namespaces ) ) {
				wfLogWarning( "JsonConfig: Invalid \$wgJsonConfigs: Namespace $ns => '$name' " .
					"is already declared as '$namespaces[$ns]'" );
			} else {
				$key = array_search( $name, $namespaces );
				if ( $key !== false ) {
					wfLogWarning( "JsonConfig: Invalid \$wgJsonConfigs: Namespace $ns => '$name' " .
						"has identical name with the namespace #$key" );
				} else {
					$namespaces[$ns] = $name;
				}
			}
		}
	}

	/**
	 * Initialize state
	 * @param Title $title
	 * @param string &$modelId
	 * @return bool
	 */
	public function onContentHandlerDefaultModelFor( $title, &$modelId ) {
		if ( !self::jsonConfigIsStorage( $this->config ) ) {
			return true;
		}

		$jct = JCSingleton::parseTitle( $title );
		if ( $jct ) {
			$modelId = $jct->getConfig()->model;
			return false;
		}
		return true;
	}

	/**
	 * Ensure that ContentHandler knows about our dynamic models (T259126)
	 * @param string[] &$models
	 */
	public function onGetContentModels( &$models ) {
		if ( !self::jsonConfigIsStorage( $this->config ) ) {
			return;
		}

		JCSingleton::init();
		// TODO: this is copied from onContentHandlerForModelID()
		$ourModels = array_replace_recursive(
			ExtensionRegistry::getInstance()->getAttribute( 'JsonConfigModels' ),
			$this->config->get( 'JsonConfigModels' )
		);
		$models = array_merge( $models, array_keys( $ourModels ) );
	}

	/**
	 * Instantiate JCContentHandler if we can handle this modelId
	 * @param string $modelId
	 * @param ContentHandler &$handler
	 * @return bool
	 */
	public function onContentHandlerForModelID( $modelId, &$handler ) {
		if ( !self::jsonConfigIsStorage( $this->config ) ) {
			return true;
		}

		JCSingleton::init();
		$models = array_replace_recursive(
			ExtensionRegistry::getInstance()->getAttribute( 'JsonConfigModels' ),
			$this->config->get( 'JsonConfigModels' )
		);
		if ( array_key_exists( $modelId, $models ) ) {
			// This is one of our model IDs
			$handler = new JCContentHandler( $modelId );
			return false;
		}
		return true;
	}

	/**
	 * AlternateEdit hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/AlternateEdit
	 * @param EditPage $editpage
	 */
	public function onAlternateEdit( $editpage ) {
		if ( !self::jsonConfigIsStorage( $this->config ) ) {
			return;
		}
		$jct = JCSingleton::parseTitle( $editpage->getTitle() );
		if ( $jct ) {
			$editpage->contentFormat = JCContentHandler::CONTENT_FORMAT_JSON_PRETTY;
		}
	}

	/**
	 * @param EditPage $editPage
	 * @param OutputPage $output
	 */
	public function onEditPage__showEditForm_initial( $editPage, $output ) {
		if (
			$output->getConfig()->get( 'JsonConfigUseGUI' ) &&
			$editPage->getTitle()->getContentModel() === 'Tabular.JsonConfig'
		) {
			$output->addModules( 'ext.jsonConfig.edit' );
		}
	}

	/**
	 * Validates that the revised contents are valid JSON.
	 * If not valid, rejects edit with error message.
	 * @param IContextSource $context
	 * @param Content $content
	 * @param Status $status
	 * @param string $summary Edit summary provided for edit.
	 * @param User $user
	 * @param bool $minoredit
	 * @return bool
	 */
	public function onEditFilterMergedContent(
		/** @noinspection PhpUnusedParameterInspection */
		IContextSource $context, Content $content, Status $status, $summary, User $user, $minoredit
	) {
		if ( !self::jsonConfigIsStorage( $this->config ) ) {
			return true;
		}

		if ( $content instanceof JCContent ) {
			$status->merge( $content->getStatus() );
			if ( !$status->isGood() ) {
				// @todo Use $status->setOK() instead after this extension
				// do not support mediawiki version 1.36 and before
				$status->setResult( false, $status->getValue() ?: EditPage::AS_HOOK_ERROR_EXPECTED );
				return false;
			}
		}
		return true;
	}

	/**
	 * Get the license code for the title or false otherwise.
	 * license code is identifier from https://spdx.org/licenses/
	 *
	 * @param JCTitle $jct
	 * @return bool|string Returns licence code string, or false if license is unknown
	 */
	private static function getTitleLicenseCode( JCTitle $jct ) {
		$jctContent = JCSingleton::getContent( $jct );
		if ( $jctContent && $jctContent instanceof JCDataContent ) {
			$license = $jctContent->getLicenseObject();
			if ( $license ) {
				return $license['code'];
			}
		}
		return false;
	}

	/**
	 * Override a per-page specific edit page copyright warning
	 *
	 * @param Title $title
	 * @param string[] &$msg
	 *
	 * @return bool
	 */
	public function onEditPageCopyrightWarning( $title, &$msg ) {
		if ( self::jsonConfigIsStorage( $this->config ) ) {
			$jct = JCSingleton::parseTitle( $title );
			if ( $jct ) {
				$code = self::getTitleLicenseCode( $jct );
				if ( $code ) {
					$msg = [ 'jsonconfig-license-copyrightwarning', $code ];
				} else {
					$requireLicense = $jct->getConfig()->license ?? false;
					// Check if page has license field to apply only if it is required
					// https://phabricator.wikimedia.org/T203173
					if ( $requireLicense ) {
						$msg = [ 'jsonconfig-license-copyrightwarning-license-unset' ];
					}
				}
				return false; // Do not allow any other hook handler to override this
			}
		}
		return true;
	}

	/**
	 * Display a page-specific edit notice
	 *
	 * @param Title $title
	 * @param int $oldid
	 * @param array &$notices
	 */
	public function onTitleGetEditNotices( $title, $oldid, &$notices ) {
		if ( self::jsonConfigIsStorage( $this->config ) ) {
			$jct = JCSingleton::parseTitle( $title );
			if ( $jct ) {
				$code = self::getTitleLicenseCode( $jct );
				if ( $code ) {
					$noticeText = wfMessage( 'jsonconfig-license-notice', $code )->parse();
					$iconCodes = '';
					if ( preg_match_all( "/[a-z][a-z0-9]+/i", $code, $subcodes ) ) {
						// Flip order due to dom ordering of the floating elements
						foreach ( array_reverse( $subcodes[0] ) as $c => $match ) {
							// Used classes:
							// * mw-jsonconfig-editnotice-icon-BY
							// * mw-jsonconfig-editnotice-icon-CC
							// * mw-jsonconfig-editnotice-icon-CC0
							// * mw-jsonconfig-editnotice-icon-ODbL
							// * mw-jsonconfig-editnotice-icon-SA
							$iconCodes .= Html::rawElement(
								'span', [ 'class' => 'mw-jsonconfig-editnotice-icon-' . $match ], ''
							);
						}
						$iconCodes = Html::rawElement(
							'div', [ 'class' => 'mw-jsonconfig-editnotice-icons skin-invert' ], $iconCodes
						);
					}

					$noticeFooter = Html::rawElement(
						'div', [ 'class' => 'mw-jsonconfig-editnotice-footer' ], ''
					);

					$notices['jsonconfig'] = Html::rawElement(
						'div',
						[ 'class' => 'mw-jsonconfig-editnotice' ],
						$iconCodes . $noticeText . $noticeFooter
					);
				} else {
					// Check if page has license field to apply notice msgs only when license is required
					// https://phabricator.wikimedia.org/T203173
					$requireLicense = $jct->getConfig()->license ?? false;
					if ( $requireLicense ) {
						$notices['jsonconfig'] = wfMessage( 'jsonconfig-license-notice-license-unset' )->parse();
					}
				}
			}
		}
	}

	/**
	 * Override with per-page specific copyright message
	 *
	 * @param Title $title
	 * @param string $type
	 * @param MessageSpecifier &$msgSpec
	 *
	 * @return bool
	 */
	public function onSkinCopyrightFooterMessage( $title, $type, &$msgSpec ) {
		if ( self::jsonConfigIsStorage( $this->config ) ) {
			$jct = JCSingleton::parseTitle( $title );
			if ( $jct ) {
				$code = self::getTitleLicenseCode( $jct );
				if ( $code ) {
					$msgSpec = Message::newFromSpecifier( 'jsonconfig-license' )
						->params( "[{{int:jsonconfig-license-url-$code}} {{int:jsonconfig-license-name-$code}}]" );
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Adds CSS for pretty-printing configuration on NS_CONFIG pages.
	 * @param OutputPage $out
	 * @param \Skin $skin
	 */
	public function onBeforePageDisplay(
		/** @noinspection PhpUnusedParameterInspection */ $out, $skin
	): void {
		if ( !self::jsonConfigIsStorage( $this->config ) ) {
			return;
		}

		$title = $out->getTitle();
		// todo/fixme? We should probably add ext.jsonConfig style to only those pages
		// that pass parseTitle()
		$handler = $this->contentHandlerFactory
			->getContentHandler( $title->getContentModel() );
		if ( $handler->getDefaultFormat() === CONTENT_FORMAT_JSON ||
			JCSingleton::parseTitle( $title )
		) {
			$out->addModuleStyles( 'ext.jsonConfig' );
		}
	}

	public function onMovePageIsValidMove(
		$oldTitle, $newTitle, $status
	) {
		if ( !self::jsonConfigIsStorage( $this->config ) ) {
			return true;
		}

		$jctOld = JCSingleton::parseTitle( $oldTitle );
		if ( $jctOld ) {
			$jctNew = JCSingleton::parseTitle( $newTitle );
			if ( !$jctNew ) {
				$status->fatal( 'jsonconfig-move-aborted-ns' );
				return false;
			} elseif ( $jctOld->getConfig()->model !== $jctNew->getConfig()->model ) {
				$status->fatal( 'jsonconfig-move-aborted-model', $jctOld->getConfig()->model,
					$jctNew->getConfig()->model );
				return false;
			}
		}

		return true;
	}

	/**
	 * Conditionally load API module 'jsondata' depending on whether or not
	 * this wiki stores any jsonconfig data
	 *
	 * @param ApiModuleManager $moduleManager Module manager instance
	 */
	public function onApiMain__moduleManager( $moduleManager ) {
		if ( $moduleManager->getConfig()->get( 'JsonConfigEnableLuaSupport' ) ) {
			$moduleManager->addModule( 'jsondata', 'action', JCDataApi::class );
		}
	}

	public function onPageSaveComplete(
		/** @noinspection PhpUnusedParameterInspection */
		$wikiPage, $user, $summary, $flags, $revisionRecord, $editResult
	) {
		return $this->onArticleChangeComplete( $wikiPage );
	}

	public function onArticleDeleteComplete(
		/** @noinspection PhpUnusedParameterInspection */
		$article, $user, $reason, $id, $content, $logEntry, $archivedRevisionCount
	) {
		return $this->onArticleChangeComplete( $article );
	}

	public function onArticleUndelete(
		/** @noinspection PhpUnusedParameterInspection */
		$title, $created, $comment, $oldPageId, $restoredPages
	) {
		return $this->onArticleChangeComplete( $title );
	}

	public function onPageMoveComplete(
		/** @noinspection PhpUnusedParameterInspection */
		$title, $newTitle, $user, $pageid, $redirid, $reason, $revisionRecord
	) {
		$title = Title::newFromLinkTarget( $title );
		$newTitle = Title::newFromLinkTarget( $newTitle );
		return $this->onArticleChangeComplete( $title ) ||
			$this->onArticleChangeComplete( $newTitle );
	}

	/**
	 * Prohibit creation of the pages that are part of our namespaces but have not been explicitly
	 * allowed.
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param array|string|MessageSpecifier &$result
	 * @return bool
	 */
	public function onGetUserPermissionsErrors(
		/** @noinspection PhpUnusedParameterInspection */
		$title, $user, $action, &$result
	) {
		if ( !self::jsonConfigIsStorage( $this->config ) ) {
			return true;
		}

		if ( $action === 'create' && JCSingleton::parseTitle( $title ) === null ) {
			// prohibit creation of the pages for the namespace that we handle,
			// if the title is not matching declared rules
			$result = 'jsonconfig-blocked-page-creation';
			return false;
		}
		return true;
	}

	/**
	 * @param \WikiPage|Title $value
	 * @param JCContent|null $content
	 * @return bool
	 */
	private function onArticleChangeComplete( $value, $content = null ) {
		if ( !self::jsonConfigIsStorage( $this->config ) ) {
			return true;
		}

		if ( $value && ( !$content || $content instanceof JCContent ) ) {
			if ( method_exists( $value, 'getTitle' ) ) {
				$value = $value->getTitle();
			}
			$jct = JCSingleton::parseTitle( $value );
			if ( $jct && $jct->getConfig()->store ) {
				$store = new JCCache( $jct, $content );
				$store->resetCache();

				// Handle remote site notification
				$store = $jct->getConfig()->store;
				// @phan-suppress-next-line PhanTypeExpectedObjectPropAccess
				if ( $store->notifyUrl ) {
					$req =
						// @phan-suppress-next-line PhanTypeExpectedObjectPropAccess
						JCUtils::initApiRequestObj( $store->notifyUrl, $store->notifyUsername,
							// @phan-suppress-next-line PhanTypeExpectedObjectPropAccess
							$store->notifyPassword );
					if ( $req ) {
						$query = [
							'format' => 'json',
							'action' => 'jsonconfig',
							'command' => 'reload',
							'title' => $jct->getNamespace() . ':' . $jct->getDBkey(),
						];
						JCUtils::callApi( $req, $query, 'notify remote JsonConfig client' );
					}
				}
			}
		}
		return true;
	}

	/**
	 * Quick check if the current wiki will store any configurations.
	 * Faster than doing a full parsing of the $wgJsonConfigs in the JCSingleton::init()
	 * @param Config $config
	 * @return bool
	 */
	public static function jsonConfigIsStorage( Config $config ) {
		static $isStorage = null;
		if ( $isStorage === null ) {
			$isStorage = false;
			$configs = array_replace_recursive(
				ExtensionRegistry::getInstance()->getAttribute( 'JsonConfigs' ),
				$config->get( 'JsonConfigs' )
			);
			foreach ( $configs as $jc ) {
				if ( ( !array_key_exists( 'isLocal', $jc ) || $jc['isLocal'] ) ||
					( array_key_exists( 'store', $jc ) )
				) {
					$isStorage = true;
					break;
				}
			}
		}
		return $isStorage;
	}
}
