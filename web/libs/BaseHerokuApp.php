<?php

require_once('BaseApp.php');

use Silex\Application;
use Silex\Provider\MonologServiceProvider;

/**
 * Herokuアプリケーション基底クラス
 * 
 * Silex & Monologを使用したアプリケーション構成
 */
abstract class BaseHerokuApp extends BaseApp {

	/**
	 * @var Application $app
	 */
	protected $app = null;

	/**
	 * @param array $config
	 */
	public function __construct(array $config) {
		parent::__construct($config);

		// アプリケーションフレームワーク
		$app = new Application();
		
		// ログ出力でmonologを使用
		$app->register(new MonologServiceProvider(), array(
			'monolog.logfile' => 'php://stderr',
			'monolog.name' => get_class($this),
		));
		$this->logger->attachMonolog($app['monolog']);
		
		$this->app = $app;
	}

	/**
	 * Silexアプリケーションの動作
	 */
	public function run() {
		$this->app->run();
	}
}