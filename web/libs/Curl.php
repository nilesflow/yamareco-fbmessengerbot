<?php

require_once('BaseClient.php');
require_once('exception/LibException.php');

/**
 * Curlクライアント 
 */
class Curl extends BaseClient {

	/**
	 * @var string
	 */
	protected $base_uri = null;

	/**
	 * @param array $options
	 */
	public function __construct(array $options) {
		$this->baseUri = $options['base_uri'];
	}

	/**
	 * HTTPリクエスト
	 *
	 * @param array $options
	 * ・headers付与
	 * ・POST指定
	 * @return string|array  response body or decoded json
	 */
	public function request(array $options) {
		$headers = array();

		// 追加ヘッダ
		if (isset($options['headers'])) {
			$headers = array_merge($headers, $options['headers']);
			$headers = $headers + $options['headers'];
		}

		// json encode.
		if ($this->getHash($options, 'encode') === 'json') {
			$headers[] = 'Content-Type: application/json';
		}

		// request start..
		$url = $this->baseUri.$options['path'];
		$ch = curl_init($url);
	
		// POST処理
		if ($this->getHash($options, 'method') == 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $options['params']);
		}
		
		// request
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($ch);
		if (! $output) {
			$errno = curl_errno($ch);
			$error = curl_error($ch);
			$header = curl_getinfo($ch, CURLINFO_HEADER_OUT);
			curl_close($ch);

			$this->logError(sprintf("%s: %s", $errno, $error));
			$this->logError($header);
			throw new CurlException("curl request failed.", $errno);
		}
		
		// Content-Typeに従った処理
		$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		$output = $this->decodeContent($contentType, $output);
		
		curl_close($ch);
		return $output;
	}
}