<?php

require('../vendor/autoload.php');

require_once('app/MountInfoBotConfig.php');
require_once('app/MountInfoFacebookBot.php');

try {
	// コンフィグ取得
	$oConfig = new MountInfoBotConfig('../config.json');
	$config = $oConfig->get();

	// Bot 処理
	$bot = new MountInfoFacebookBot($config);
	$bot->run();
}
catch (Exception $e) {
	error_log($e->getMessage());
}
