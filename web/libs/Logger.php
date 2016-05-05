<?php

/**
 * ログ出力制御
 */
class Logger extends Base {
	/**
	 * ファイル出力設定
	 * @var array $basic
	 */
	protected $basic = [
		'isValid' => false,
		'filename' => null,
	];

	/**
	 * Monologインスタンス
	 * @var Monolog\Logger $monolog
	 */
	protected $monolog = null;

	/**
	 * ファイル出力のマッピング
	 * @var array $mapBasic
	 */
	protected $mapBasic = [
		'debug'	=> "[DEBUG]",
		'info'	=> "[INFO]",
		'warn'	=> "[WARN]",
		'error'	=> "[ERROR]",
		'fatal' => "[FATAL]",
	];

	/**
	 * Monolog出力のマッピング
	 * @var array $mapMonolog
	 */
	protected $mapMonolog = [
		'debug'	=> 'addDebug',
		'info'	=> 'addInfo',
		'warn'	=> 'addWarning',
		'error'	=> 'addError',
		'fatal' => 'addError',
	];

	/**
	 * コンストラクタ
	 * 
	 * ファイル出力の設定
	 * @param string $filename 出力ファイル名
	 */
	public function __construct($config) {
		if (!isset($config['BASIC'])) {
			return;
		}
		$basic = $config['BASIC'];

		$this->basic['isValid'] = $basic['VALID'];
		$this->basic['filename'] = $basic['DIR'].'/'.$basic['FILENAME'];
	}

	/**
	 * Monologのアタッチ
	 * @param Monolog\Logger $monolog
	 */
	public function attachMonolog(Monolog\Logger $monolog) {
		$this->monolog = $monolog;
	}

	/**
	 * @param mixed $output
	 */
	public function debug($output) {
		$this->output(__FUNCTION__, $output);
	}

	/**
	 * @param mixed $output
	 */
	public function info($output) {
		$this->output(__FUNCTION__, $output);
	}

	/**
	 * @param mixed $output
	 */
	public function warn($output) {
		$this->output(__FUNCTION__, $output);
	}

	/**
	 * @param mixed $output
	 */
	public function error($output) {
		$this->output(__FUNCTION__, $output);
	}

	/**
	 * @param mixed $output
	 */
	public function fatal($output) {
		$this->output(__FUNCTION__, $output);
	}

	/**
	 * ログ出力
	 * 
	 * ファイル or Monolog 指示された方法で出力
	 * @param mixed $output 出力対象
	 */
	protected function output($method, $output) {
		if (is_array($output) || is_object($output)) {
			$output = print_r($output, true);
		}
		$output .= "\n";

		// PHPベースの出力処理
		if ($this->basic['isValid']) {
			// prefix の取得
			$prefix = $this->getHash($this->mapBasic, $method, "");
			$output = $prefix." ".$output;

			// ファイル出力
			file_put_contents($this->basic['filename'], $output, FILE_APPEND);
		}

		// Monologの出力処理
		if (! is_null($this->monolog)) {
			// 対応メソッドの取得
			$methodMonolog = $this->getHash($this->mapMonolog, $method, null);

			// 出力
			if (! is_null($methodMonolog)) {
				call_user_func(array($this->monolog, $methodMonolog), $output);
			}
		}
	}
}
