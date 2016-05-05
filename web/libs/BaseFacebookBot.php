<?php

require_once('BaseHerokuApp.php');
require_once('FacebookMessengerInterface.php');

use Symfony\Component\HttpFoundation\Request;

/**
 * Facebook Messenger Bot基底クラス
 * 
 * Herokuアプリケーションを継承
 */
abstract class BaseFacebookBot extends BaseHerokuApp {

	/**
	 * @var FacebookMessengerInterface
	 */
	protected $messenger = null;

	/**
	 * @param array $config
	 */
	public function __construct(array $config) {
		parent::__construct($config);

		// Facebook Messager Bot の実体生成
		$this->messenger = $this->initMessenger($config);
		$this->messenger->attachLogger($this->logger);

		/**
		 * route設定
		 */ 
		$_config = $config['FACEBOOK_MESSANGER_PLATFORM'];

		// 認証
		// @link https://developers.facebook.com/docs/messenger-platform/quickstart
		$this->app->get($_config['ENDPOINT'], function (Request $request) {
			return $this->messenger->setup($request->query->all());
		});
		
		// コールバック
		// @link https://developers.facebook.com/docs/messenger-platform/webhook-reference
		$this->app->post($_config['ENDPOINT'], function (Request $request) {
			$this->messenger->webhook($request->getContent());
			return 0;
		});
	}

	/**
	 * @param array $config
	 * @return FacebookMessengerInterface
	 */
	abstract protected function initMessenger(array $config);
}