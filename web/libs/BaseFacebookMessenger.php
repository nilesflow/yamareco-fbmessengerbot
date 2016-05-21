<?php

require_once('BaseRunnable.php');
require_once('FacebookMessengerApi.php');
require_once('FacebookMessengerInterface.php');

/**
 * Facebook Messenger Platformの基底クラス
 * 
 * @link https://developers.facebook.com/docs/messenger-platform 
 */
abstract class BaseFacebookMessenger extends BaseRunnable implements FacebookMessengerInterface {

	/**
	 * Verify Token
	 * @var string
	 */
	protected $verifyToken = null;

	/**
	 * Facebook Messenger APIインスタンス
	 * @var FacebookMessengerApi
	 */
	protected $facebook = null;

	/**
	 * @param array $config
	 */
	public function __construct(array $config) {
		$this->verifyToken = $config['FACEBOOK_MESSANGER_PLATFORM']['VERIFY_TOKEN'];
		$this->facebook = new FacebookMessengerApi($config['FACEBOOK_MESSANGER_API']);
	
		// ログ出力用
		$this->addAttachee($this->facebook);
	}
	
	/**
	 * {@inheritDoc}
	 * @see FacebookMessengerInterface::setup()
	 */
	public function setup(array $query) {
		$response = "";
		try {
			if ($this->getHash($query, 'hub_verify_token') === $this->verifyToken) {
				// 初回に、WelcomeMessage設定
				$this->initWelcomeMessage();
	
				// return "verify ok".
				$response = $this->getHash($query, 'hub_challenge');
				$this->logInfo('verify ok.');
			}
		}
		catch (Exception $e) {
			$this->logFatal($e->getMessage());
		}
		return $response;
	}
	
	/**
	 * {@inheritDoc}
	 * @see FacebookMessengerInterface::webhook()
	 */
	public function webhook($contents) {
		try {
//			$this->logDebug($contents);
			$body = json_decode($contents, true);
		
			// Array containing event data
			foreach ($body['entry'] as $obj) {
				$this->logInfo(sprintf('obj: %s', json_encode($obj)));
		
				// Object containing data related to messaging
				foreach ($obj['messaging'] as $m) {
					$this->logInfo(sprintf('messaging: %s', json_encode($m)));
	
					// internal process.
					$from = $m['sender']['id'];
					$this->receivedMessaging($from, $m);
				}
			}
		}
		catch (Exception $e) {
			$this->logFatal($e->getMessage());
		}
		return 0;
	}

	/**
	 * WelcomeMessageの設定
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#welcome_message_configuration
	 */
	abstract protected function initWelcomeMessage();

	/**
	 * Message-Received Callback, Text Message
	 * @param string $from
	 * @param string $text
	 * @link https://developers.facebook.com/docs/messenger-platform/webhook-reference#received_message
	 */
	abstract protected function receivedText($from, $text);
	
	/**
	 * Message-Received Callback, Message with Image, Video or Audio Attachment
	 * @param string $from
	 * @param string $attachment
	 * @link https://developers.facebook.com/docs/messenger-platform/webhook-reference#received_message
	 */
	abstract protected function receivedAttachment($from, $attachment);
	
	/**
	 * Postback Callback
	 * @param string $from
	 * @param string $payload
	 * @link https://developers.facebook.com/docs/messenger-platform/webhook-reference#postback
	 */
	abstract protected function receivedPostback($from, $payload);

	/**
	 * Message-Received Callback
	 * @param string $from
	 * @param array $messaging
	 */
	protected function receivedMessaging($from, array $messaging) {
		// Message-Received Callback
		// @link https://developers.facebook.com/docs/messenger-platform/webhook-reference#received_message
		$message = $this->getHash($messaging, 'message');
		if (! is_null($message)) {
			// Text Message
			$text = $this->getHash($message, 'text');
			if (!is_null($text)) {
				$this->receivedText($from, $text);
			}
	
			// Message with Image, Video or Audio Attachment：未使用
			$attachment = $this->getHash($message, 'attachments');
			if (!is_null($attachment)) {
				$this->receivedAttachment($from, $attachment);
			}

			// Message-Delivered Callback
			// @todo
		}

		// Postback Callback
		// @link https://developers.facebook.com/docs/messenger-platform/webhook-reference#postback
		$postback = $this->getHash($messaging, 'postback');
		if (! is_null($postback)) {
			$payload = $this->getHash($postback, 'payload');
			if (!is_null($payload)) {
				$this->receivedPostback($from, $payload);
			}
		}
	}

	/**
	 * Send Request, Generic template 共通処理
	 * 
	 * ボタン、要素の最大数を考慮した汎用処理
	 * @param string $id
	 * @param array $sources
	 * @param callable $callback
	 */
	protected function sendGenericElements($id, array $sources, callable $callback) {
		$response = null;
	
		// コンテンツを生成・返却
		$element = [];
		$elements = [];
		foreach($sources as $i => $source) {
			// コールバックで要素生成
			if (! $callback($i, $source, $element)) {
				continue;
			}
			$elements[] = $element;
			$element = [];
	
			// facebookの最大値
			if (count($elements) >= FacebookMessengerApi::LIMIT_BUBBLES_PER_MESSAGE) {
				// 一度送信
				$response = $this->facebook->sendGeneric($id, $elements);
				$elements = [];
			}
		}
	
		// 残った分を送信
		if (count($elements) !== 0) {
			$elements[] = $element;
			$response = $this->facebook->sendGeneric($id, $elements);
		}
		return $response;
	}
}