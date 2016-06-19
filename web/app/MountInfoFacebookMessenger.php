<?php

require_once('MountInfoException.php');
require_once(dirname(__FILE__).'/../libs/YamarecoApi.php');
require_once(dirname(__FILE__).'/../libs/BaseFacebookMessenger.php');

/**
 * 山の情報を調べて返すBot on Fecebook Messenger
 * 
 * Webhookのメッセージを取得、ヤマレコAPIを使って MessengerへSned
 */
class MountInfoFacebookMessenger extends BaseFacebookMessenger {
	/**
	 * @var YamarecoApi $yamareco
	 */
	protected $yamareco = null;

	/**
	 * @param array $config
	 */
	public function __construct(array $config) {
		parent::__construct($config);

		// ヤマレコAPI ＆ ログ出力設定
		$this->yamareco = new YamarecoApi($config['YAMARECO_API']);
		$this->addAttachee($this->yamareco);
	}

	/**
	 * {@inheritDoc}
	 * @see BaseFacebookMessenger::initWelcomeMessage()
	 */
	protected function initWelcomeMessage() {
		try {
			$this->configWelcome();
		}
		// include, 
		// FacebookMessengerApiException
		// LibException
		catch (Exception $e) {
			$this->logFatal($e->getMessage());
			$text = "Sorry.. エラーが発生しました。";
			$this->sendNextAction($from, $text);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see BaseFacebookMessenger::receivedMessaging()
	 * ユーザー情報の取得とエラー処理
	 */
	protected function receivedMessaging($from, array $messaging) {
		// メッセージ返信の例外処理
		try {
			// ユーザー情報取得
			$user = $this->facebook->getUserProfile($from);
			// ページでわかるので、ログに出すだけ
			$this->logInfo($user);

			parent::receivedMessaging($from, $messaging);
		}
		catch (MountInfoException $e) {
			$text = "Sorry.. ".$e->getMessage();
			$this->sendNextAction($from, $text);
		}
		catch (YamarecoApiException $e) {
			switch ($e->errcode) {
				case 'NODATA':
					$text = "Sorry.. データが見つかりませんでした。";
					break;
				default:
					$text = "Sorry.. エラーが発生しました。";
					break;
			}
			$this->sendNextAction($from, $text);
		}
		// include, 
		// FacebookMessengerApiException
		// LibException
		catch (Exception $e) {
			$this->logFatal($e->getMessage());
			$text = "Sorry.. エラーが発生しました。";
			$this->sendNextAction($from, $text);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see BaseFacebookMessenger::receivedText()
	 * テキストによる検索処理
	 */
	protected function receivedText($from, $text) {
		$this->searchPoi($from, ['text' => $text]);
	}

	/**
	 * {@inheritDoc}
	 * @see BaseFacebookMessenger::receivedAttachment()
	 * @todo 対応するアクション
	 */
	protected function receivedAttachment($from, $attachment) {
		$text = "ファイルの受信に対応するアクションはありません。";
		throw new MountInfoException($text);
	}

	/**
	 * {@inheritDoc}
	 * @see BaseFacebookMessenger::receivedPostback()
	 * postbackの実行
	 * 定義したコマンドを実行
	 */
	protected function receivedPostback($from, $payload) {
		$hPl = json_decode($payload, true);
		
		// 自分が発行したコマンドに応じた処理
		$command = $this->getHash($hPl, 'command');
		switch($command) {
			// 検索結果からの次ページ検索
			case 'nextpage':
				$this->searchPoi($from, $hPl);
				break;
			// 近くの情報を検索
			case 'nearby':
				$this->nearbyPoi($from, $hPl);
				break;
			// 近くの情報を検索の次ページ検索
			case 'nextpagenearby':
				$this->nearbyPoi($from, $hPl);
				break;
			// メニュー
			case 'menu':
				$this->sendMenu($from);
				break;
			// エリアリストの取得
			case 'arealist':
				$this->searchAreaList($from);
				break;
			// エリアを指定して検索
			case 'searcharea':
				$this->searchPoi($from, $hPl);
				break;
			// データ種別リストの取得
			case 'typelist':
				$this->searchTypeList($from);
				break;
				// データ種別を指定して検索
			case 'searchtype':
				$this->searchPoi($from, $hPl);
				break;
			default:
				throw new MountInfoException('処理中にエラーが発生しました。');
		}
	}

	/**
	 * テキストからPoiを検索して結果を返答
	 * @param string $id
	 * @param array $hCond
	 * @throws MountInfoException
	 */
	protected function searchPoi($id, $hCond) {
		$text		= $this->getHash($hCond, 'text', "");
		$page		= $this->getHash($hCond, 'page', 1);
		$area_id	= $this->getHash($hCond, 'area_id', 0);
		$type_id	= $this->getHash($hCond, 'type_id', 0);

		// 文字列でなければ続行不可
		if (! is_string($text)) {
			throw new MountInfoException("情報の取得に失敗しました。");
		}
		
		// ヤマレコ：地名データの検索
		$res = $this->yamareco->searchPoi($text, $page, $area_id, $type_id);
		//		$this->printDebug(sprintf('pois: %s', json_encode($res)));
		
		// 検索情報をFacebookへ送信
		$this->sendPoiInfo($id, $res['poilist']);
		
		// 次アクションの案内をFacebookへ送信
		$found = count($res['poilist']);
		$this->sendSearchResult($id, $found, $hCond);
	}

	/**
	 * Poi取得結果の送信
	 * @param string $id
	 * @param array $pois
	 */
	protected function sendPoiInfo($id, $pois) {
		// 検索結果・表示情報を送信
		$this->sendPoiSummary($id, count($pois));

		// 地名データ情報を送信
		$this->sendPois($id, $pois);
	}

	/**
	 * 緯度経度による近隣情報の検索
	 * 
	 * 検索範囲は最大
	 * @param string $id
	 * @param array $hCond
	 */
	protected function nearbyPoi($id, $hCond) {
		$lat	= $this->getHash($hCond, 'lat', 0);
		$lon	= $this->getHash($hCond, 'lon', 0);
		$page	= $this->getHash($hCond, 'page', 1);
	
		// ヤマレコ：地名データの検索
		$res = $this->yamareco->nearbyPoi($lat, $lon, $page, YamarecoApi::RANGE_MAX);
	
		// 検索情報をFacebookへ送信
		$this->sendPoiInfo($id, $res['poilist']);
	
		// 次アクションの案内をFacebookへ送信
		$found = count($res['poilist']);
		$this->sendNearbyResult($id, $found, $hCond);
	}

	/**
	 * エリアリストの取得、返答
	 * @param string $id
	 */
	protected function searchAreaList($id) {
		// ヤマレコから取得
		$res = $this->yamareco->getAreaList();
	
		// 取得結果をFacebookへ送信
		$this->sendAreaList($id, $res['arealist']);

		// ガイダンス送信
		$text = "検索したいエリアを選択してください。";
		$this->facebook->sendText($id, $text);
	}

	/**
	 * データ種別リストの取得、返答
	 * @param string $id
	 */
	protected function searchTypeList($id) {
		// ヤマレコから取得
		$res = $this->yamareco->getTypeList();
	
		// 取得結果をFacebookへ送信
		$this->sendTypeList($id, $res['typelist']);

		// ガイダンス送信
		$text = "検索したいオプションを選択してください。";
		$this->facebook->sendText($id, $text);
	}

	/**
	 * Poi取得サマリーの送信
	 * @param string $id
	 * @param int $found
	 * @return array
	 */
	protected function sendPoiSummary($id, $found) {
		// facebook messageの1テンプレート中の最大要素数
		$max = FacebookMessengerApi::LIMIT_BUBBLES_PER_MESSAGE;

		$text = "";
		// 取得件数を返却
		if ($found > $max) {
			$text = $max.'件ずつ表示します。';
		}
		$text .= "\n※画面によっては横スクロールされない場合があります。";
		return $this->facebook->sendText($id, $text);
	}

	/**
	 * Poi取得結果の送信
	 * @param string $id
	 * @param array $pois
	 * @return array
	 */
	protected function sendPois($id, array $pois) {
		// 各要素をコールバックで生成してテンプレートを送信
		return $this->sendGenericElements($id, $pois, function($i, $poi, &$element){
			$element = [];
			$element['title'] = $poi['name']." ".$poi['yomi'];
			$element['item_url'] = $poi['page_url'];
			$element['image_url'] = $poi['photo_url'];
			// 山以外の情報は標高がない場合が有る
			if ($poi['elevation'] != "0") {
				$element['subtitle'] = $poi['elevation']."m ";
			}
			else {
				$element['subtitle'] = "";
			}
			$element['subtitle'] .= $poi['detail'];
			$element['buttons'] = [
				[
					'type' => 'web_url',
					'title' => "Google Mapで開く",
					'url' => "https://maps.google.com/maps?q=".$poi['lat'].",".$poi['lon']."&ll=".$poi['lat'].",".$poi['lon']."&z=10&".$poi['name'],
				],
				[
					'type' => 'postback',
					'title' => "近くの情報を検索",
					'payload' => json_encode([
						'command' => 'nearby', 'lat' => $poi['lat'], 'lon' => $poi['lon'], 'name' => $poi['name']
					])
				],
			];
			return true;
		});
	}

	/**
	 * Poi検索結果情報の送信
	 * @param string $id
	 * @param int $found
	 * @param array $hCond
	 * @return array
	 */
	protected function sendSearchResult($id, $found, array $hCond) {
		$text		= $this->getHash($hCond, 'text', "");
		$page		= $this->getHash($hCond, 'page', 1);
		$area_id	= $this->getHash($hCond, 'area_id', 0);
		$type_id	= $this->getHash($hCond, 'type_id', 0);
		$area_name	= $this->getHash($hCond, 'area_name', "");
		$type_name	= $this->getHash($hCond, 'type_name', "");
		
		$buttons = [];

		// ヤマレコからの取得結果が最大数以上なら次ページを案内
		$max = YamarecoApi::LIMIT_POIS_PER_PAGE;
		if ($found >= $max) {
			$payload = json_encode([
				'command' => 'nextpage', 'text' => $text, 'page' => ($page + 1),
					'area_id' => $area_id, 'type_id' => $type_id, 'area_name' => $area_name, 'type_name' => $type_name
			]);
			$buttons[] = $this->makeNextButton($page, $payload);
		}

		// 次アクション
		$buttonText = "";
		if ($text !== "") {
			$buttonText = "\"{$text}\"のキーワード検索\n";
		}
		if ($area_name !== "") {
			$buttonText = "\"{$area_name}\"のエリア指定検索\n";
		}
		if ($type_name !== "") {
			$buttonText = "\"{$type_name}\"のオプション情報検索\n";
		}
		$to		= (($page - 1) * $max) + 1;
		$from	= (($page - 1) * $max) + $found;
		$buttonText .= $to."～".$from."件目の情報を表示しています。";
		return $this->sendNextAction($id, $buttonText, $buttons);
	}

	/**
	 * 近隣情報検索結果情報の送信
	 * @param string $id
	 * @param int $found
	 * @param array $hCond
	 * @return array
	 */
	protected function sendNearbyResult($id, $found, $hCond) {
		$lat	= $this->getHash($hCond, 'lat', 0);
		$lon	= $this->getHash($hCond, 'lon', 0);
		$page	= $this->getHash($hCond, 'page', 1);
		$name	= $this->getHash($hCond, 'name', "");
		
		$buttons = [];

		// ヤマレコからの取得結果が最大数以上なら次ページを案内
		$max = YamarecoApi::LIMIT_POIS_PER_PAGE;
		if ($found >= $max) {
			$hPayload = 
			$payload = json_encode([
				'command' => 'nextpagenearby', 'lat' => $lat, 'lon' => $lon, 'page' => ($page + 1),
					'name' => $name
			]);
			$buttons[] = $this->makeNextButton($page, $payload);
		}

		// 次アクション
		if ($name !== "") {
			$buttonText = '"'.$name.'"近隣の検索\n';
		}
		$to		= (($page - 1) * $max) + 1;
		$from	= (($page - 1) * $max) + $found;
		$buttonText = $to."～".$from."件目の情報を表示しています。";
		return $this->sendNextAction($id, $buttonText, $buttons);
	}

	/**
	 * エリアリストの送信 
	 * @param string $id
	 * @param array $areas
	 * @return array
	 */
	protected function sendAreaList($id, array $areas) {
		// 各要素をコールバックで生成してテンプレートを送信
		return $this->sendGenericElements($id, $areas, function($i, $area, &$element){
			$maxButtons = FacebookMessengerApi::LIMIT_CALLTOACTION_ITEMS;

			if ($i % $maxButtons === 0) {
				$element = [];
				$element['title'] = ($i / $maxButtons)."ページ";
				$element['buttons'] = [];
			}
			$element['buttons'][] = [
				'type' => 'postback',
				'title' => $area['area'],
				'payload' => json_encode([
					'command' => 'searcharea', 'area_id' => $area['area_id'], 'area_name' => $area['area']
				]),
			];
			if ($i % $maxButtons !== ($maxButtons -1)) {
				return false;
			}
			return true;
		});
	}

	/**
	 * データ種別の送信
	 * @param string $id
	 * @param array $types
	 * @return array
	 */
	protected function sendTypeList($id, array $types) {
		// 各要素をコールバックで生成してテンプレートを送信
		return $this->sendGenericElements($id, $types, function($i, $type, &$element){
			$maxButtons = FacebookMessengerApi::LIMIT_CALLTOACTION_ITEMS;

			if ($i % $maxButtons === 0) {
				$element = [];
				$element['title'] = ($i / $maxButtons)."ページ";
				$element['buttons'] = [];
			}
			$title = $type['name'];
			if ($type['comment'] !== "") {
				$title .= "(".$type['comment'].")";
			}
			$element['buttons'][] = [
				'type' => 'postback',
				'title' => $title,
				'payload' => json_encode([
					'command' => 'searchtype', 'type_id' => $type['type_id'], 'type_name' => $type['name']
				]),
			];
			if ($i % $maxButtons !== ($maxButtons -1)) {
				return false;
			}
			return true;
		});
	}

	/**
	 * 共通処理：次検索の案内ボタン生成
	 * @param unknown $page
	 * @param string $payload json
	 */
	protected function makeNextButton($page, $payload) {
		$nextNum  = $page * YamarecoApi::LIMIT_POIS_PER_PAGE + 1;
		$button = [
			'type' => 'postback',
			'title' => $nextNum."件目以降の情報をリクエスト",
			'payload' => $payload,
		];
		return $button;
	}

	/**
	 * 共通処理：他の検索方法のボタン生成
	 * @return array
	 */
	protected function makeMenuButtons() {
		$buttons = [
			[
				'type' => 'postback',
				'title' => "エリアを選択して検索",
				'payload' => json_encode(['command' => 'arealist']),
			],
			[
				'type' => 'postback',
				'title' => "オプション情報を選択して検索",
				'payload' => json_encode(['command' => 'typelist']),
			],
		];
		return $buttons;
	}

	/**
	 * Welcome Message 設定
	 * @reurn array
	 */
	protected function configWelcome() {
		$text = "はじめまして！\n調べたい山のキーワードをメッセージで送ってください。\nまたは、他の方法でも検索できます。";
		$buttons = $this->makeMenuButtons();
		return $this->facebook->configWelcomeButton($text, $buttons);
	}

	/**
	 * 他の検索方法のボタンを送信
	 * @return array
	 */
	protected function sendMenu($id) {
		$text = "他の検索方法も選択できます。";
		$buttons = $this->makeMenuButtons();
		return $this->facebook->sendButton($id, $text, $buttons);
	}

	/**
	 * 他の検索方法の案内ボタンを送信
	 * @return array
	 */
	protected function sendNextAction($id, $text, $buttons = []) {
		// デフォルト：メニュー
		$buttons[] = [
			'type' => 'postback',
			'title' => "他の方法で検索",
			'payload' => json_encode(['command' => 'menu']),
		];
		return $this->facebook->sendButton($id, $text, $buttons);
	}
}
