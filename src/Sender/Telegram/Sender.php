<?php
namespace ddSendFeedback\Sender\Telegram;

class Sender extends \ddSendFeedback\Sender\Sender {
	/**
	 * @property $botToken {string} — Токен бота в вида 'botId:HASH'. @required
	 * @property $chatId {string_numeric} — ID чата, в который слать сообщение. @required
	 * @property $messageMarkupSyntax {'Markdown'|'HTML'|''} — Синтаксис, в котором написано сообщение. Default: ''.
	 * @property $disableWebPagePreview {boolean} — Disables link previews for links in this message. Default: false.
	 * @property $proxy {string} — Proxy server in format 'protocol://user:password@ip:port'. E. g. 'asan:gd324ukl@11.22.33.44:5555' or 'socks5://asan:gd324ukl@11.22.33.44:5555'. Default: —.
	 */
	protected
		$botToken = '',
		$chatId = '',
		$messageMarkupSyntax = '',
		$disableWebPagePreview = false,
		$proxy = '',
		
		$requiredProps = [
			'botToken',
			'chatId'
		]
	;
	
	private
		$url = 'https://api.telegram.org/bot[+botToken+]/sendMessage?chat_id=[+chatId+]&text=[+text+]&parse_mode=[+messageMarkupSyntax+]&disable_web_page_preview=[+disableWebPagePreview+]'
	;
	
	public function __construct($params = []){
		//Call base constructor
		parent::__construct($params);
		
		//Prepare “messageMarkupSyntax”
		$this->messageMarkupSyntax = trim($this->messageMarkupSyntax);
		//Allowable values
		if (!in_array(
			$this->messageMarkupSyntax,
			[
				'Markdown',
				'HTML',
				''
			]
		)){
			$this->messageMarkupSyntax = '';
		}
		
		//Prepare “disableWebPagePreview”
		$this->disableWebPagePreview = boolval($this->disableWebPagePreview);
	}
	
	/**
	 * send
	 * @version 1.2.2 (2019-12-14)
	 * 
	 * @desc Send messege to a Telegram chat.
	 * 
	 * @return $result {array} — Returns the array of send status.
	 * @return $result[0] {0|1} — Status.
	 */
	public function send(){
		$result = [];
		
		if ($this->canSend){
			$result = [0 => 0];
			
			//Отсылаем сообщение
			$requestResult = \ddTools::$modx->runSnippet(
				'ddMakeHttpRequest',
				[
					'url' => \ddTools::parseText([
						'text' => $this->url,
						'data' => [
							'botToken' => $this->botToken,
							'chatId' => $this->chatId,
							'text' => urlencode($this->text),
							'messageMarkupSyntax' => $this->messageMarkupSyntax,
							'disableWebPagePreview' => intval($this->disableWebPagePreview)
						],
						'mergeAll' => false
					]),
					'proxy' => $this->proxy
				]
			);
			
			//Разбиваем пришедшее сообщение
			$requestResult = json_decode(
				$requestResult,
				true
			);
			
			//Everything is ok
			if (
				is_array($requestResult) &&
				isset($requestResult['ok']) &&
				$requestResult['ok'] == true
			){
				$result[0] = 1;
			}else{
				//Если ошибка, то залогируем
				\ddTools::logEvent([
					'message' => '<code><pre>' . print_r(
						$requestResult,
						true
					) . '</pre></code>',
					'source' => 'ddSendFeedback → Telegram',
					'eventType' => 'error'
				]);
			}
		}
		
		return $result;
	}
}
?>