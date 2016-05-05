<?php

require_once('BaseApi.php');
require_once('exception/FacebookMessengerApiException.php');

/**
 * Facebook Messenger Platform
 * Send API Reference
 *
 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference
 */
class FacebookMessengerApi extends BaseApi {
	const BASE_URI = 'https://graph.facebook.com/v2.6/';

	// @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#request
	const LIMIT_TEXT = 320; // char
	
	// @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#guidelines
	const LIMIT_TITIE = 45; // char
	const LIMIT_SUBTITLE = 80; // char
	const LIMIT_CALLTOACTION_TITIE = 20; // char
	const LIMIT_CALLTOACTION_ITEMS = 3; // buttons
	const LIMIT_BUBBLES_PER_MESSAGE = 10; // elements

	// @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#request
	const NOTIFICATION_TYPE_REGULAR = 'REGULAR';
	const NOTIFICATION_TYPE_SILENT_PUSH = 'SILENT_PUSH';
	const NOTIFICATION_TYPE_NO_PUSH = 'NO_PUSH';

	/**
	 * Welcome Messege設定で使用
	 * @var string $pageId
	 */
	protected $pageId = null;

	/**
	 * @var string
	 */
	protected $pageAccessToken = null;

	/**
	 * @param array $config
	 */
	public function __construct(array $config) {
		$config['BASE_URI'] = self::BASE_URI;
		parent::__construct($config);

		$this->pageId = $config['PAGE_ID'];
		$this->pageAccessToken = $config['PAGE_ACCESS_TOKEN'];
	}

	/**
	 * Send Request - text
	 * @param string $id
	 * @param string $text
	 * @param string $notifType
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#request
	 */
	public function sendText($id, $text, $notifType = self::NOTIFICATION_TYPE_REGULAR) {
		// メッセージ生成
		$message = $this->makeMessageText($text);
		// メッセージ送信共通処理
		return $this->sendMessages($id, $message, $notifType);
	}

	/**
	 * Send Request - attachment - image payload
	 * @param string $id
	 * @param string $url
	 * @param string $notifType
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#request
	 */
	public function sendImage($id, $url, $notifType = self::NOTIFICATION_TYPE_REGULAR) {
		$attachment = $this->makeAttachmentImage($url);
		return $this->sendAttachment($id, $attachment, $notifType);
	}

	/**
	 * Send Request - attachment - template payload - Generic template
	 * @param string $id
	 * @param array $elements
	 * @param string $notifType
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#request
	 */
	public function sendGeneric($id, array $elements, $notifType = self::NOTIFICATION_TYPE_REGULAR) {
		$payload = $this->makePayloadGeneric($elements);
		return $this->sendTemplate($id, $payload, $notifType);
	}

	/**
	 * Send Request - attachment - template payload - Button template
	 * @param string $id
	 * @param string $text
	 * @param array $buttons
	 * @param string $notifType
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#request
	 */
	public function sendButton($id, $text, array $buttons, $notifType = self::NOTIFICATION_TYPE_REGULAR) {
		$payload = $this->makePayloadButton($text, $buttons);
		return $this->sendTemplate($id, $payload, $notifType);
	}

	/**
	 * Send Request - attachment - template payload - Receipt template
	 * @param string $id
	 * @param string $recipient_name
	 * @param string $order_number
	 * @param string $currency
	 * @param string $payment_method
	 * @param string $elements
	 * @param string $summary
	 * @param string $notifType
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#request
	 */
	public function sendReceipt($id, $recipient_name, $order_number, $currency, 
		$payment_method, $elements, $summary, $notifType = self::NOTIFICATION_TYPE_REGULAR) {
		$payload = $this->makePayloadReceipt($recipient_name, $order_number, $currency,
				$payment_method, $elements, $summary);
		return $this->sendTemplate($id, $payload, $notifType);
	}

	/**
	 * Welcome Message - Configuration - Text
	 * @param string $text
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#welcome_message_configuration
	 */
	public function configWelcomeText($text) {
		$message = $this->makeMessageText($text);
		return $this->configWelcomeMessage($message);
	}

	/**
	 * Welcome Message - Configuration - image payload
	 * @param string $url
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#welcome_message_configuration
	 */
	public function configWelcomeImage($url) {
		$attachment = $this->makeAttachmentImage($url);
		return $this->configWelcomeAttachment($attachment);
	}

	/**
	 * Welcome Message - Configuration - template payload - Generic template
	 * @param array $elements
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#welcome_message_configuration
	 */
	public function configWelcomeGeneric(array $elements) {
		$payload = $this->makePayloadGeneric($elements);
		return $this->configWelcomeTemplate($payload);
	}

	/**
	 * Welcome Message - Configuration - template payload - Button template
	 * @param string $text
	 * @param array $buttons
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#welcome_message_configuration
	 */
	public function configWelcomeButton($text, array $buttons) {
		$payload = $this->makePayloadButton($text, $buttons);
		return $this->configWelcomeTemplate($payload);
	}

	/**
	 * Welcome Message - Configuration - template payload - Receipt template
	 * @param string $recipient_name
	 * @param string $order_number
	 * @param string $currency
	 * @param string $payment_method
	 * @param string $elements
	 * @param string $summary
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#welcome_message_configuration
	 */
	public function configWelcomeReceipt($recipient_name, $order_number, $currency,
		$payment_method, $elements, $summary) {
		$payload = $this->makePayloadReceipt($recipient_name, $order_number, $currency,
				$payment_method, $elements, $summary);
		return $this->configWelcomeTemplate($payload);
	}

	/**
	 * Welcome Message - Delete
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#welcome_message_configuration
	 */
	public function deleteWelcome() {
		return $this->configWelcome();
	}

	/**
	 * User Profile
	 * @param string $id
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#user_profile_request
	 */
	public function getUserProfile($id) {
		$options = [];
		$options['method'] = 'GET';
		$options['path'] = $id;
		$options['params'] = [
			'fields' => "first_name,last_name,profile_pic",
			'access_token' => $this->pageAccessToken
		];
		return $this->request($options);
	}

	/**
	 * 共通処理：make template payload - Generic template
	 * @param array $elements
	 * @return array
	 */
	protected function makePayloadGeneric(array $elements) {
		$payload = [
			'template_type' => 'generic',
			'elements' => $elements
		];
		return $payload;
	}

	/**
	 * 共通処理：make template payload - Button template
	 * @param array $buttons
	 * @return array
	 */
	protected function makePayloadButton($text, array $buttons) {
		$payload = [
			'template_type' => 'button',
			'text' => mb_substr($text, 0, self::LIMIT_TEXT), // under limit
			'buttons' => $buttons
		];
		return $payload;
	}

	/**
	 * 共通処理：make template payload - Receipt template
	 * @param string $recipient_name
	 * @param string $order_number
	 * @param string $currency
	 * @param string $payment_method
	 * @param string $elements
	 * @param string $summary
	 * @return array
	 */
	protected function makePayloadReceipt($recipient_name, $order_number, $currency, 
		$payment_method, $elements, $summary) {
		$payload = [
			'template_type' => 'receipt',
			'recipient_name' => $recipient_name,
			'order_number' => $order_number,
			'currency' => $currency,
			'payment_method' => $payment_method,
			'elements' => $elements,
			'summary' => $summary,
		];
		return $payload;
	}

	/**
	 * 共通処理：make attachment - template payload
	 * @param array $payload
	 * @return array
	 */
	protected function makeAttachmentTemplate(array $payload) {
		$attachment = [
			'type' => 'template',
			'payload' => $payload,
		];
		return $attachment;
	}

	/**
	 * 共通処理：make attachment - image payload
	 * @param string $url
	 * @return array
	 */
	protected function makeAttachmentImage($url) {
		$attachment = [
			'type' => 'image',
			'payload' => [
				'url' => $url,
			],
		];
		return $attachment;
	}

	/**
	 * 共通処理：make text
	 * @param string $text
	 * @return array
	 */
	protected function makeMessageText($text) {
		$message = [];
		$message['text'] = mb_substr($text, 0, self::LIMIT_TEXT); // under limit
		return $message;
	}

	/**
	 * 共通処理：make attachment
	 * @param array $attachment
	 * @return array
	 */
	protected function makeMessageAttachment(array $attachment) {
		$message = [];
		$message['attachment'] = $attachment;
		return $message;
	}

	/**
	 * 共通処理：Send attachment - template payload
	 * @param string $id
	 * @param array $payload
	 * @param string $notifType
	 * @return array
	 */
	protected function sendTemplate($id, array $payload, $notifType) {
		$attachment = $this->makeAttachmentTemplate($payload);
		return $this->sendAttachment($id, $attachment, $notifType);
	}

	/**
	 * 共通処理：Send attachment
	 * @param string $id
	 * @param array $attachment
	 * @param string $notifType
	 * @return array
	 */
	protected function sendAttachment($id, array $attachment, $notifType) {
		$message = $this->makeMessageAttachment($attachment);
		return $this->sendMessages($id, $message, $notifType);
	}

	/**
	 * 共通処理：Welcome Message attachment - template payload
	 * @param array $payload
	 * @return array
	 */
	protected function configWelcomeTemplate(array $payload) {
		$attachment = $this->makeAttachmentTemplate($payload);
		return $this->configWelcomeAttachment($attachment);
	}
	
	/**
	 * 共通処理：Welcome Message attachment
	 * @param array $attachment
	 * @return array
	 */
	protected function configWelcomeAttachment(array $attachment) {
		$message = $this->makeMessageAttachment($attachment);
		return $this->configWelcomeMessage($message);
	}

	/**
	 * 共通処理：Send API Request
	 * @param string $id
	 * @param array $message
	 * @param string $notifType
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#request
	 */
	protected function sendMessages($id, array $message, $notifType) {
		$options = [];
		$options['method'] = 'POST';
		$options['path'] = sprintf('me/messages?access_token=%s', $this->pageAccessToken);
		$options['params'] = [
			'recipient' => [
				'id' => $id,
			],
			'message' => $message,
			'notification_type' => $notifType
		];
		return $this->request($options);
	}


	/**
	 * 共通処理：Welcome Message Configuration/Delete
	 * @param array $message
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#welcome_message_configuration
	 */
	protected function configWelcomeMessage(array $message) {
		$call_to_actions = [
			[
				'message' => $message,
			]
		];
		return $this->configWelcome($call_to_actions);
	}

	/**
	 * 共通処理：Welcome Message
	 * @param array $call_to_actions
	 * @return array
	 * @link https://developers.facebook.com/docs/messenger-platform/send-api-reference#welcome_message_configuration
	 */
	protected function configWelcome(array $call_to_actions = array()) {
		$options = [];
		$options['method'] = 'POST';
		$options['path'] = sprintf('%s/thread_settings?access_token=%s', $this->pageId, $this->pageAccessToken);
		$options['params'] = [
			'setting_type' => 'call_to_actions',
			'thread_state' => 'new_thread',
			'call_to_actions' => $call_to_actions
		];
		return $this->request($options);
	}

	/**
	 * {@inheritDoc}
	 * @return array
	 * @see BaseApi::request()
	 * @throws FacebookMessengerApiException
	 */
	protected function request(array $options) {
		$json = parent::request($options);
		// Content-Typeによるデコードが効かないので試みる
		$arr = json_decode($json, true);
		if (is_null($arr)) {
			$this->logError($json);
			throw new FacebookMessengerApiException("json_decode failed.");
		}
		// @see https://developers.facebook.com/docs/messenger-platform/send-api-reference#errors
		if (isset($arr['error'])) {
			$this->logError($arr['error']);
			$code = $this->getHash($arr, 'code', 0);
			throw new FacebookMessengerApiException("messanger api error.", $code);
		}
		return $arr;
	}
}