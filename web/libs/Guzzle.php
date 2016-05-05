<?php 

require_once('BaseClient.php');
require_once('exception/LibException.php');

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

/**
 * GuzzleHTTPクライアント
 */
class Guzzle extends BaseClient {

	/**
	 * @var GuzzleHttp\Client $guzzle
	 */
	protected $guzzle = null;

	/**
	 * コンストラクタ
	 * 
	 * Base URIを指定してGuzzleインスタンスを生成
	 * @param array $options
	 */
	public function __construct(array $options) {
		$this->guzzle = new Client($options);
	}

	/**
	 * HTTPリクエスト
	 *
	 * @param array $options
	 * ・Content-Type:application/json 指定
	 * ・POST指定
	 * @return string|array  response body or decoded json
	 */
	public function request($options) {
		$gOptions = [];
		$params = $this->getHash($options, 'params', array());
		if ($this->getHash($options, 'encode') === 'json') {
			$gOptions = ['json' => $params];
		}
		else {
			if ($this->getHash($options, 'method') === 'POST') {
				$gOptions = ['form_params' => $params];
			}
			else {
				if (count($params) !== 0) {
					$gOptions = ['query' => $params];
				}
			}
		}
		try {
			$oResponse = $this->guzzle->request($options['method'], $options['path'], $gOptions);
		}
		catch (BadResponseException $e) {
			$this->logError($e->getMessage());
			$oResponse = $e->getResponse();
		}
		if (! is_object($oResponse)) {
			$this->logError($options);
			$this->logError($oResponse);
			throw new LibException("guzzle request failed.");
		}
		$body = $oResponse->getBody()->getContents();

		$contentType = $oResponse->getHeader('Content-Type');
		$body = $this->decodeContent($contentType[0], $body);
		
		return $body;
	}
}