<?php

require_once('Base.php');

/**
 * 基底クラス
 * 
 * ログ出力管理等
 */
abstract class BaseRunnable extends Base {

	/**
	 * @var Logger $logger
	 */
	protected $logger = null;

	/**
	 * @var array
	 */
	protected $attachees = [];
	
	/**
	 * ロガーインスタンスのアタッチ
	 *
	 * @param Logger $logger ロガークラスインスタンス
	 */
	public function attachLogger($logger) {
		$this->logger = $logger;
	
		// 指示されるクラスにも同様に
		$this->attachOthers($logger, __METHOD__);
	}

	/**
	 * ログ出力
	 * @param mixed $output 出力メッセージ
	 */
	protected function logDebug($output) {
		$this->logOutput('debug', $output);
	}

	protected function logInfo($output) {
		$this->logOutput('info', $output);
	}

	protected function logWarn($output) {
		$this->logOutput('warn', $output);
	}
	
	protected function logError($output) {
		$this->logOutput('error', $output);
	}

	protected function logFatal($output) {
		$this->logOutput('fatal', $output);
	}

	/**
	 * 制御可能なクラスのみ保持
	 * @param object $obj
	 */
	protected function addAttachee($obj) {
		if ($obj instanceof BaseRunnable) {
			$this->attachees[] = $obj;
		}
	}

	/**
	 * ログ出力汎用クラス
	 * 
	 * Loggerの対応メソッドをコール
	 * @param string $method
	 * @param mixed $output
	 */
	protected function logOutput($method, $output) {
		if (! is_null($this->logger)) {
			call_user_func(array($this->logger, $method), $output);
		}
	}

	/**
	 * ログ出力クラス設定を指示されたインスタンスにも伝搬
	 * @param object $obj
	 * @param string $method
	 */
	protected function attachOthers($obj, $method) {
		foreach ($this->attachees as $attach){
			call_user_func(array($attach, $method), $obj);
		}
	}
}
