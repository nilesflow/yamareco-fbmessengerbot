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

                // Fatal Errorの捕捉
                set_error_handler(array($this, 'exception_error_handler'));
	}


	/**
	 * 実行時エラーの捕捉
	 *
	 * Parse Error等は捕捉できない。
	 */
        public function exception_error_handler($severity, $message, $file, $line) {
                throw new ErrorException($message, 0, $severity, $file, $line);
        }

}
