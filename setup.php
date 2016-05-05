<?php
/**
 * セットアップ
 */
require_once('web/Config.php');

// コンフィグ生成
$path = 'config.json';
if (! file_exists($path)) {
	$ret = copy('config-default.json', 'config.json');
	if (!$ret) {
		echo 'creating config.json failed. path:'.$path."\n";
	}
}
else {
	echo 'config.json already has created.';
}

$config = new Config('config.json');
$CONFIG = $config->get();

// ディレクトリ生成
$path = $CONFIG['LOGGER']['BASIC']['DIR'];
if (! is_dir($path)) {
	$ret = mkdir($path, 0777, TRUE);
	if (!$ret) {
		echo 'mkdir failed. path:'.$path."\n";
	}
	chmod($path, 0777);
}
