<?php

require_once('BaseRunnable.php');
require_once('exception/LibException.php');

/**
 * HTTPクライアント共通処理
 */
abstract class BaseClient extends BaseRunnable {

	/**
	 * Content-Typeに従ったデコード
	 * 
	 * @param sring $contentType
	 * @param string $body JSON
	 * @throws LibException
	 */
	protected function decodeContent($contentType, $body) {
		if (strstr($contentType, 'application/json;')) {
			$decoded = json_decode($body, true);
			if (is_null($decoded)) {
				$this->logError($body);
				throw new LibException("json_decode for response failed.");
			}
			$body = $decoded;
		}
		return $body;
	}
}

