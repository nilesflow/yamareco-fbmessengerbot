<?php

/**
 * Yamareco API Exception
 */
class YamarecoApiException extends LibException {

	/**
         * error_code
         * @var string
         */
	public $errcode;

	/**
	 * @param string $message
	 * @param string $errcode
	 */
	public function __construct($message, $errcode) {
		$this->errcode = $errcode;
 		parent::__construct($message);
	}
}
