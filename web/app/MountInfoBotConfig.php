<?php

require_once(dirname(__FILE__).'/../libs/Config.php');

/**
 * 山の情報を調べて返すBot コンフィグ管理クラス
 * 
 * 共通読み込み処理＋herokuで設定する環境変数を上書き
 */
class MountInfoBotConfig extends Config {
	/**
	 * @param string $path
	 */
	public function __construct($path) {
		parent::__construct($path);
		
		// for production: Herokuのenv変数を上書き
		$pageId = getenv('FACEBOOK_PAGE_ID');
		if ($pageId) {
			$this->config['FACEBOOK_MESSANGER_API']['PAGE_ID'] = $verifyToken;
		}
		$accessToken = getenv('FACEBOOK_PAGE_ACCESS_TOKEN');
		if ($accessToken) {
			$this->config['FACEBOOK_MESSANGER_API']['PAGE_ACCESS_TOKEN'] = $accessToken;
		}
		$verifyToken = getenv('FACEBOOK_PAGE_VERIFY_TOKEN');
		if ($verifyToken) {
			$this->config['FACEBOOK_MESSANGER_PLATFORM']['VERIFY_TOKEN'] = $verifyToken;
		}
	}
}