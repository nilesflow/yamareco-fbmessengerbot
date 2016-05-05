<?php

require_once('Base.php');
require_once('exception/LibException.php');

/**
 * コンフィグ管理クラス
 * 
 * 指定されたjsonのコンフィグファイルを取得、保持
 */
class Config extends Base {

	/**
	 * @var array $config
	 */
	protected $config = null;

	/**
	 * @param string $path
	 * @throws LibException
	 */
	public function __construct($path) {
		// 基本形式をjsonから
		$json = file_get_contents($path);
		if (!$json) {
			throw new LibException('config file invalid.');
		}
		$config = json_decode($json, true);
		if (!$config) {
			throw new LibException('config format invalid.');
		}
		$this->config = $config;
	}

	/**
	 * コンフィグ情報取得
	 */
	public function get() {
		return $this->config;
	}
}
 