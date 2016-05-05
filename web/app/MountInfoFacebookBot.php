<?php

require_once(dirname(__FILE__).'/../libs/BaseFacebookBot.php');
require_once('MountInfoFacebookMessenger.php');

/**
 * 山の情報を調べて返すBot アプリケーション
 * 
 * index.php からキックされる
 */
class MountInfoFacebookBot extends BaseFacebookBot {
	
	protected function initMessenger(array $config) {
		// Facebook Messenger Bot生成
		return new MountInfoFacebookMessenger($config);
	}
}