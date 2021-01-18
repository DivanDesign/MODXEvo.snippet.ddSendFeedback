<?php
/**
 * ddSendFeedback
 * @version 2.5 (2019-12-15)
 * 
 * @desc A snippet for sending users' feedback messages to a required email, Slack and Telegram chats or SMS through sms.ru. It is very useful along with ajax technology.
 * 
 * @uses PHP >= 5.4
 * @uses (MODX)EvolutionCMS >= 1.1
 * @uses (MODX)EvolutionCMS.libraries.ddTools >= 0.32 {@link https://code.divandesign.biz/modx/ddtools }
 * @uses (MODX)EvolutionCMS.snippets.ddMakeHttpRequest >= 1.4 {@link https://code.divandesign.biz/modx/ddmakehttprequest }
 * 
 * General:
 * @param $result_titleSuccess {string} — The title that will be returned if the letter sending is successful (the «title» field of the returned JSON). Default: 'Message sent successfully'.
 * @param $result_titleFail {string} — The title that will be returned if the letter sending is failed somehow (the «title» field of the returned JSON). Default: 'Unexpected error =('.
 * @param $result_messageSuccess {string} — The message that will be returned if the letter sending is successful (the «message» field of the returned JSON). Default: 'We will contact you later.'.
 * @param $result_messageFail {string} — The message that will be returned if the letter sending is failed somehow (the «message» field of the returned JSON). Default: 'Something happened while sending the message.<br />Please try again later.'.
 * 
 * Senders:
 * @param $senders {stringJson|stringQueryFormated|arrayAssociative|object} — JSON (https://en.wikipedia.org/wiki/JSON) or Query-formated string (https://en.wikipedia.org/wiki/Query_string) determining which senders should be used.
 * @param $senders[item] {arrayAssociative} — Key is a sender name, value is sender parameters.
 * Senders → Email:
 * @param senders->email {arrayAssociative} — Sender params. Send method (PHP mail() or SMTP) sets up in CMS config.
 * @param senders->email->to {array|stringCommaSeparated} — Mailing addresses (to whom). @required
 * @param senders->email->to[i] {stringEmail} — An address. @required
 * @param senders->email->tpl {stringChunkName|string} — The template of a letter (chunk name or code via “@CODE:” prefix). Available placeholders: [+docId+] — the id of a document that the request has been sent from; the array components of $_POST. Use [(site_url)][~[+docId+]~] to generate the url of a document ([(site_url)] is required because of need for using the absolute links in the emails). @required
 * @param senders->email->tpl_placeholders {arrayAssociative} — Additional data has to be passed into `senders->email->tpl`. Default: ''.
 * @param senders->email->tpl_placeholders[item] {string} — Key — a placeholder name, value — a placeholder value. Default: ''.
 * @param senders->email->subject {string} — Message subject. Default: 'Feedback'.
 * @param senders->email->from {string} — Mailer address (from who). Default: $modx->getConfig('emailsender').
 * @param senders->email->fileInputNames {array|stringCommaSeparated} — Input tags names separated by commas that files are required to be taken from. Used if files are sending in the request ($_FILES array). Default: ''.
 * Senders → Slack:
 * @param senders->slack {arrayAssociative} — Sender params.
 * @param senders->slack->url {string_url} — WebHook URL. @required
 * @param senders->slack->tpl {stringChunkName|string} — The template of a message (chunk name or code via `@CODE:` prefix). Available placeholders: [+docId+] — the id of a document that the request has been sent from; the array components of $_POST. Use [(site_url)][~[+docId+]~] to generate the url of a document ([(site_url)] is required because of need for using the absolute links in the messages). @required
 * @param senders->slack->tpl_placeholders {arrayAssociative} — Additional data has to be passed into `senders->slack->tpl`. Default: ''.
 * @param senders->slack->tpl_placeholders[item] {string} — Key — a placeholder name, value — a placeholder value. Default: ''.
 * @param senders->slack->channel {string} — Channel in Slack to send. Default: Selected in Slack when you create WebHook.
 * @param senders->slack->botName {string} — Bot name. Default: 'ddSendFeedback'.
 * @param senders->slack->botIcon {string} — Bot icon. Default: ':ghost:'.
 * Senders → Telegram:
 * @param senders->telegram {arrayAssociative} — Sender params.
 * @param senders->telegram->botToken {string} — Токен бота, который будет отправлять сообщение, вида “botId:HASH”. @required
 * @param senders->telegram->chatId {string} — ID чата, в который будет отправлено сообщение. @required
 * @param senders->telegram->tpl {stringChunkName|string} — The template of a message (chunk name or code via “@CODE:” prefix). Available placeholders: [+docId+] — the id of a document that the request has been sent from; the array components of $_POST. @required
 * @param senders->telegram->textMarkupSyntax {'markdown'|'html'|''} — Синтаксис, в котором написано сообщение. Default: ''.
 * @param senders->telegram->disableWebPagePreview {boolean} — Disables link previews for links in this message. Default: false.
 * @param senders->telegram->proxy {string} — Proxy server in format 'protocol://user:password@ip:port'. E. g. 'asan:gd324ukl@11.22.33.44:5555' or 'socks5://asan:gd324ukl@11.22.33.44:5555'. Default: —.
 * Senders → Sms.ru:
 * @param senders->smsru {arrayAssociative} — Sender params.
 * @param senders->smsru->apiId {string} — Secret code from sms.ru. @required
 * @param senders->smsru->tpl {stringChunkName|string} — The template of a message (chunk name or code via “@CODE:” prefix). Available placeholders: [+docId+] — the id of a document that the request has been sent from; the array components of $_POST. @required
 * @param senders->smsru->to {string} — A phone. @required
 * @param senders->smsru->from {string} — Sms sender name/phone.
 * Senders → Custom HTTP request:
 * @param senders->customhttprequest {arrayAssociative} — Sender params.
 * @param senders->customhttprequest->url {string} — The URL to request. @required
 * @param senders->customhttprequest->tpl {stringChunkName|string} — The template of a data (chunk name or code via “@CODE:” prefix). Available placeholders: [+docId+] — the id of a document that the request has been sent from; the array components of $_POST. Use [(site_url)][~[+docId+]~] to generate the url of a document ([(site_url)] is required because of need for using the absolute links in the messages). @required
 * @param senders->customhttprequest->tpl_placeholders {arrayAssociative} — Additional data has to be passed into `senders->customhttprequest->tpl`. Default: ''.
 * @param senders->customhttprequest->tpl_placeholders->{$name} {string} — Key — a placeholder name, value — a placeholder value. Default: ''.
 * @param senders->customhttprequest->method {'get'|'post'} — Request type. Default: 'post'.
 * @param senders->customhttprequest->headers {stringQueryFormated|array} — An array of HTTP header fields to set. E. g. '0=Accept: application/vnd.api+json&1=Content-Type: application/vnd.api+json'. Default: —.
 * @param senders->customhttprequest->userAgent {string} — The contents of the 'User-Agent: ' header to be used in a HTTP request. Default: —.
 * @param senders->customhttprequest->timeout {integer} — The maximum number of seconds for execute request. Default: 60.
 * @param senders->customhttprequest->proxy {string} — Proxy server in format 'protocol://user:password@ip:port'. E. g. 'http://asan:gd324ukl@11.22.33.44:5555' or 'socks5://asan:gd324ukl@11.22.33.44:5555'. Default: —.
 *  
 * @example &senders=`{
 * 	"email": {
 * 		"to": "info@divandesign.biz",
 * 		"tpl": "general_letters_feedbackToEmail",
 * 		"tpl_placeholders": {"testPlaceholder": "test"}
 * 	},
 * 	"slack": {
 * 		"url": "https://hooks.slack.com/services/WEBHOOK",
 * 		"tpl": "general_letters_feedbackToSlack"
 * 	},
 * 	"smsru": {
 * 		"apiId": "00000000-0000-0000-0000-000000000000",
 * 		"to": "89999999999",
 * 		"tpl": "general_letters_feedbackToSMS"
 * 	},
 * 	"telegram": {
 * 		"botToken": "123:AAAAAA",
 * 		"chatId": "-11111",
 * 		"tpl": "@CODE:Test message from [(site_url)]!"
 * 	}
 * }`.
 * @example &senders=`email[to]=info@divandesign.biz&email[tpl]=general_letters_feedbackToEmail&email[tpl_placeholders][testPlaceholder]=test&slack[url]=https://hooks.slack.com/services/WEBHOOK&slack[tpl]=general_letters_feedbackToSlack&smsru[to]=89999999999&smsru[tpl]=general_letters_feedbackToSMS&smsru[apiId]=00000000-0000-0000-0000-000000000000`.
 * 
 * @return {stringJson}
 * 
 * @link https://code.divandesign.biz/modx/ddsendfeedback
 * 
 * @copyright 2010–2019 DD Group {@link https://DivanDesign.biz }
 */

namespace ddSendFeedback;

$snippetPath =
	$modx->getConfig('base_path') .
	'assets/snippets/ddSendFeedback/'
;

//TODO: Remove it
if(
	is_file(
		$modx->config['base_path'] .
		'vendor/autoload.php'
	)
){
	require_once(
		$modx->getConfig('base_path') .
		'vendor/autoload.php'
	);
}

//Include (MODX)EvolutionCMS.libraries.ddTools
if(!class_exists('\ddTools')){
	require_once(
		$modx->getConfig('base_path') .
		'assets/libs/ddTools/modx.ddtools.class.php'
	);
}

if(!class_exists('\ddSendFeedback\Sender\Sender')){
	require_once(
		$snippetPath .
		'require.php'
	);
}

$result = \ddTools::getResponse();
$result_meta = [
	//Bad Request (required parameters are not set)
	'code' => 400,
	'success' => false
];

//Senders is required parameter
if (isset($senders)){
	//Получаем язык админки
	$lang = $modx->getConfig('manager_language');
	
	//Если язык русский
	if(
		$lang == 'russian-UTF8' ||
		$lang == 'russian'
	){
		$result_titleSuccess =
			isset($result_titleSuccess) ?
			$result_titleSuccess :
			'Заявка успешно отправлена'
		;
		$result_titleFail =
			isset($result_titleFail) ?
			$result_titleFail :
			'Непредвиденная ошибка =('
		;
		$result_messageSuccess =
			isset($result_messageSuccess) ?
			$result_messageSuccess :
			'Наш специалист свяжется с вами в ближайшее время.'
		;
		$result_messageFail =
			isset($result_messageFail) ?
			$result_messageFail :
			'Во время отправки заявки что-то произошло.<br />Пожалуйста, попробуйте чуть позже.'
		;
	}else{
		$result_titleSuccess =
			isset($result_titleSuccess) ?
			$result_titleSuccess :
			'Message sent successfully'
		;
		$result_titleFail =
			isset($result_titleFail) ?
			$result_titleFail :
			'Unexpected error =('
		;
		$result_messageSuccess =
			isset($result_messageSuccess) ?
			$result_messageSuccess :
			'We will contact you later.'
		;
		$result_messageFail =
			isset($result_messageFail) ?
			$result_messageFail :
			'Something happened while sending the message.<br />Please try again later.'
		;
	}
	
	$outputMessages = [
		'titles' => [
			0 => $result_titleFail,
			1 => $result_titleSuccess
		],
		'messages' => [
			0 => $result_messageFail,
			1 => $result_messageSuccess
		]
	];
	
	$sendResults = [];
	
	$senders = \ddTools::encodedStringToArray($senders);
	
	//Iterate through all senders to create their instances
	foreach(
		$senders as
		$senderName =>
		$senderParams
	){
		$sender = \ddSendFeedback\Sender\Sender::createChildInstance([
			'name' => $senderName,
			'parentDir' =>
				$snippetPath .
				'src' .
				DIRECTORY_SEPARATOR .
				'Sender'
			,
			//Passing parameters to senders's constructor
			'params' => $senderParams
		]);
		
		//Send message (items with integer keys are not overwritten)
		$sendResults = array_merge(
			$sendResults,
			$sender->send()
		);
	}
	
	//Fail by default
	$sendResults_status = 0;
	
	//Перебираем все статусы отправки
	foreach (
		$sendResults as
		$sendResults_item
	){
		//Запоминаем
		$sendResults_status = intval($sendResults_item);
		
		//Если не отправлось хоть на один адрес, считаем, что всё плохо
		if ($sendResults_status == 0){
			break;
		}
	}
	
	$result_meta['message'] = [
		'content' => $outputMessages['messages'][$sendResults_status],
		'title' => $outputMessages['titles'][$sendResults_status]
	];
	
	$result_meta['success'] = boolval($sendResults_status);
	
	if ($result_meta['success']){
		$result_meta['code'] = 200;
	};
}

$result->setMeta($result_meta);

return $result->toJSON();
?>