<?php

require_once('BaseRunnable.php');
require_once('Logger.php');

/**
 * アプリ共通クラス
 */
abstract class BaseApp extends BaseRunnable {

	/**
	 * コンストラクタ
	 * 
	 * ログ出力モジュールの生成と保持 
	 * @param array $config
	 */
	public function __construct(array $config) {
		// ログ出力設定
		$logger = new Logger($config['LOGGER']);
		$this->attachLogger($logger);
	}
}
