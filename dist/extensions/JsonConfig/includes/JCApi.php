<?php
namespace JsonConfig;

use ApiBase;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * Allows JsonConfig to be manipulated via API
 */
class JCApi extends ApiBase {

	private static function addStatusConf( $conf ) {
		// explicitly list values to avoid accidental exposure of private data
		$res = [
			'model' => $conf->model,
			'namespace' => $conf->namespace,
			'nsName' => $conf->nsName,
			'nsTalk' => isset( $conf->nsTalk ) && $conf->nsTalk ? $conf->nsTalk : 'default',
			'isLocal' => $conf->isLocal,
			'cacheExp' => $conf->cacheExp,
			'cacheKey' => $conf->cacheKey,
			'flaggedRevs' => $conf->flaggedRevs,
		];
		if ( isset( $conf->remote ) ) {
			$res['remote'] = [
				'url' => $conf->remote->url,
				'username' => $conf->remote->username !== '', // true or false
				'password' => $conf->remote->password !== '', // true or false
			];
		}
		if ( isset( $conf->store ) ) {
			// when store equals to false it's a flag to indicate content is stored externaly
			$res['store'] = [
				'cacheNewValue' => $conf->store ? $conf->store->cacheNewValue : null,
				'notifyUrl' => $conf->store ? $conf->store->notifyUrl : null,
				'notifyUsername' => $conf->store ? $conf->store->notifyUsername !== '' : false,
				'notifyPassword' => $conf->store ? $conf->store->notifyPassword !== '' : false,
			];
		}
		return $res;
	}

	public function execute() {
		$result = $this->getResult();

		$params = $this->extractRequestParams();
		$command = $params['command'];

		switch ( $command ) {
			case 'status':
				$this->getMain()->setCacheMaxAge( 1 * 30 ); // seconds
				$this->getMain()->setCacheMode( 'public' );

				global $wgJsonConfigModels;
				$result->addValue(
					null,
					'models',
					\ExtensionRegistry::getInstance()->getAttribute( 'JsonConfigModels' )
					+ $wgJsonConfigModels
				);

				$data = [];
				foreach ( JCSingleton::getTitleMap() as $ns => $confs ) {
					$vals = [];
					foreach ( $confs as $conf ) {
						$vals[] = self::addStatusConf( $conf );
					}
					$data[$ns] = $vals;
				}
				if ( $data ) {
					$result->setIndexedTagName( $data, 'ns' );
				}
				$result->addValue( null, 'titleMap', $data );
				break;

			case 'reset':
			case 'reload':
				// FIXME: this should be POSTed, not GETed.
				// This code should match JCSingleton::onArticleChangeComplete()
				// Currently, that action is not used because in production store->notifyUrl is null
				// Can MW API allow both for the same action, or should it be a separate action?

				$this->getMain()->setCacheMaxAge( 1 ); // seconds
				$this->getMain()->setCacheMode( 'private' );
				if ( !$this->getUser()->isAllowed( 'jsonconfig-flush' ) ) {
					// Sigh. Can't use $this->checkUserRightsAny() because
					// this has to break API conventions by returning 401
					// (and violate the HTTP RFC by doing so without a
					// WWW-Authenticate header).
					$this->dieWithError(
						[
							'apierror-permissiondenied',
							$this->msg( "action-jsonconfig-flush" )
						],
						'permissiondenied', [], 401
					);
				}
				if ( !isset( $params['namespace'] ) ) {
					$this->dieWithError(
						[ 'apierror-jsonconfig-paramrequired', 'namespace' ],
						'badparam-namespace'
					);
				}
				if ( !isset( $params['title'] ) ) {
					$this->dieWithError(
						[ 'apierror-jsonconfig-paramrequired', 'title' ], 'badparam-title'
					);
				}

				$jct = JCSingleton::parseTitle( $params['title'], $params['namespace'] );
				if ( !$jct ) {
					$this->dieWithError( 'apierror-jsonconfig-badtitle', 'badparam-titles' );
				}

				if ( isset( $params['content'] ) && $params['content'] !== '' ) {
					if ( $command !== 'reload ' ) {
						$this->dieWithError(
							[
								'apierror-invalidparammix-mustusewith',
								'content',
								'command=reload'
							],
							'badparam-content'
						);
					}
					$content = JCSingleton::parseContent( $jct, $params['content'], true );
				} else {
					$content = false;
				}

				$jc = new JCCache( $jct, $content );
				if ( $command === 'reset' ) {
					$jc->resetCache( false ); // clear cache
				} elseif ( $content ) {
					$jc->resetCache( true ); // set new value in cache
				} else {
					$jc->get(); // gets content from the default source and cache
				}

				break;
		}
	}

	public function getAllowedParams() {
		return [
			'command' => [
				ParamValidator::PARAM_DEFAULT => 'status',
				ParamValidator::PARAM_TYPE => [
					'status',
					'reset',
					'reload',
				]
			],
			'namespace' => [
				ParamValidator::PARAM_TYPE => 'integer',
			],
			'title' => '',
			'content' => '',
		];
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 * @return array
	 */
	protected function getExamplesMessages() {
		return [
			'action=jsonconfig&format=json'
				=> 'apihelp-jsonconfig-example-1',
			'action=jsonconfig&command=reset&namespace=486&title=Brazil/Amazonas.map&format=json'
				=> 'apihelp-jsonconfig-example-2',
			'action=jsonconfig&command=reload&namespace=486&title=Brazil/Amazonas.map&format=json'
				=> 'apihelp-jsonconfig-example-3',
		];
	}
}
