<?php

require_once('BaseRunnable.php');
require_once('Curl.php');
require_once('Guzzle.php');
require_once('exception/LibException.php');

/**
 * Web API共通処理
 */
abstract Class BaseApi extends BaseRunnable {
	/**
	 * @var string
	 */
	protected $driver = null;
	/**
	 * @var Curl
	 */
	protected $curl = null;
	/**
	 * @var Guzzle
	 */
	protected $guzzle = null;

	/**
	 * コンストラクタ
	 * 
	 * Web APIクラスで使用するHTTPクライアントのセットアップ
	 * 
	 * @param array $config
	 * @throws LibException
	 */
	public function __construct(array $config) {
		$this->driver = $config['DRIVER'];
		switch ($this->driver) {
			case 'curl':
				$options = ['base_uri' => $config['BASE_URI']];
				$this->curl = new Curl($options);
				$this->addAttachee($this->curl);
				break;
			case 'guzzle':
				$options = ['base_uri' => $config['BASE_URI']];
				$this->guzzle = new Guzzle($options);
				$this->addAttachee($this->guzzle);
				break;
			default:
				throw new LibException('undefined driver.');
		}
	}

	/**
	 * リクエスト
	 * 
	 * 選択されたHTTPクライアントを使用してリクエスト
	 * 
	 * @param array $options
	 * @return string|array  response body or decoded json
	 * @throws LibException
	 */
	protected function request(array $options) {
		// ドライバ毎の処理
		switch ($this->driver) {
			case 'curl':
				$response = $this->curl->request($options);
				break;
			case 'guzzle':
				$response = $this->guzzle->request($options);
				break;
			default:
				throw new LibException('undefined driver.');
		}

		return $response;
	}
}