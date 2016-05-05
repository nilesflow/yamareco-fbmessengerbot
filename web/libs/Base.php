<?php

/**
 * 基底クラス
 * 
 * ユーティリティ系処理の実装
 * 全Class共通
 */
abstract class Base {

	/**
	 * 連想配列の値取得
	 * 値が存在しない場合に指定したデフォルト値を返却
	 * 
	 * @param array $target
	 * @param mixed $key
	 * @param mixed $default
	 * @return mixed 配列の値 or 指定した初期値
	 */
	protected function getHash(array $target, $key, $default = null) {
		if (! isset($target[$key])) {
			return $default;
		}
		return $target[$key];
	}
}
