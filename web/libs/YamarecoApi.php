<?php

require_once('BaseApi.php');
require_once('exception/YamarecoApiException.php');

/**
 * ヤマレコ Web API
 *
 * @link https://sites.google.com/site/apiforyamareco/api/api_other
 */
class YamarecoApi extends BaseApi {
	const BASE_URI = 'https://api.yamareco.com/api/v1/';

	const LIMIT_POIS_PER_PAGE = 20;

	// nearbyPoi
	const RANGE_MIN = 1; // km
	const RANGE_MAX = 30; // km

	/**
	 * @param array $config
	 */
	public function __construct(array $config) {
		$config['BASE_URI'] = self::BASE_URI;
		parent::__construct($config);
	}

	/**
	 * エリアリストの取得
	 * @return array
	 */
	public function getAreaList() {
		$options = [];
		$options['path'] = 'getArealist';
		$options['method'] = 'GET';
		return $this->request($options);
	}

	/**
	 * ジャンルリストの取得
	 * @return array
	 */
	public function getGenreList() {
		$options = [];
		$options['path'] = 'getGenrelist';
		$options['method'] = 'GET';
		return $this->request($options);
	}

	/**
	 * 地名データの検索
	 * @param string $name 検索したい地名の名称。
	 * @param int $page ページ番号
	 * @param int $type_id 検索したい地名のデータ種別 0：未指定
	 * @param int $area_id 検索したい地名のエリアID 0:検索条件に入れない
	 * @param int $ptid 表示したい地名のID 0:検索条件に入れない
	 * @return array
	 */
	public function searchPoi($name = "", $page = 1, $area_id = 0, $type_id = 0, $ptid = 0) {
		$options = [];
		$options['path'] = 'searchPoi';
		$options['method'] = 'POST';
		$options['params'] = [
			'page'		=> $page, 
			'name'		=> $name,
			'type_id'	=> $type_id,
			'area_id'	=> $area_id,
			'ptid' 		=> $ptid 
		];
		return $this->request($options);
	}

	/**
	 * 近隣の地名データ一覧
	 * @param float $lat
	 * @param float $lon
	 * @param int $page ページ番号
	 * @param int $range
	 * @param int $type_id 検索したい地名のデータ種別 0：未指定
	 * @return array
	 */
	public function nearbyPoi($lat, $lon, $page = 1, $range = self::RANGE_MIN, $type_id = 0) {
		$options = [];
		$options['path'] = 'nearbyPoi';
		$options['method'] = 'POST';
		$options['params'] = [
			'page'		=> $page,
			'range'		=> $range,
			'lat'		=> $lat,
			'lon'		=> $lon,
			'type_id' 	=> $type_id
		];
		return $this->request($options);
	}


	/**
	 * 地名データのデータ種別リストの取得 
	 * @return array
	 */
	public function getTypeList() {
		$options = [];
		$options['path'] = 'getTypelist';
		$options['method'] = 'GET';
		return $this->request($options);
	}
	
	/**
	 * {@inheritDoc}
	 * @return array
	 * @see Api::request()
	 * @throws YamarecoApiException
	 */
	protected function request(array $options) {
		$aOutput = parent::request($options);
		if ($this->getHash($aOutput, 'err', 0) !== 0) {
			$this->logError($aOutput);
			throw new YamarecoApiException("yamareco api error.", 'NODATA');
		}
		return $aOutput;
	}
}
