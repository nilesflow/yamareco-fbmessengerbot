<?php

/**
 * Facebook Messenger Platform Webhook インタフェース 
 */
interface FacebookMessengerInterface {

	/**
	 * Webhooksのセットアップ
	 * @param array $query
	 * @link https://developers.facebook.com/docs/messenger-platform/quickstart
	 */
	public function setup(array $query);
	
	/**
	 * Webhook
	 * @param string $contents
	 * @link https://developers.facebook.com/docs/messenger-platform/webhook-reference#common_format
	 */
	public function webhook($contents);

}
