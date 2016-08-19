<?php
/**
 * ddSendFeedback.php
 * @version 1.9.2 (2016-06-17)
 * 
 * @desc A snippet for sending users' feedback messages to a required email. It is very useful along with ajax technology.
 * 
 * @uses The library MODX.ddTools 0.15.4.
 * 
 * @param $email {comma separated string} — Mailing addresses (to whom). @required
 * @param $email_docField {string} — Field name/TV containing the address to mail. Default: —.
 * @param $email_docId {integer} — ID of a document with the required field contents. Default: —.
 * @param $tpl {string: chunkName} — The template of a letter (chunk name). Available placeholders: [+docId+] — the id of a document that the request has been sent from; the array components of $_POST. Use [(site_url)][~[+docId+]~] to generate the url of a document ([(site_url)] is required because of need for using the absolute links in the emails). @required
 * @param $text {string} — Message text. The template parameter will be ignored if the text is defined. It is useful when $modx->runSnippets() uses. Default: ''.
 * @param $subject {string} — Message subject. Default: 'Feedback'.
 * @param $from {string} — Mailer address (from who). Default: 'info@divandesign.biz'.
 * @param $from_formField {string} — An element of $_POST containing mailer address. The “from” parameter will be ignored if “from_formField” is defined and is not empty. Default: ''.
 * @param $filesFields {comma separated string} — Input tags names separated by commas that files are required to be taken from. Used if files are sending in the request ($_FILES array). Default: ''.
 * @param $result_titleSuccess {string} — The title that will be returned if the letter sending is successful (the «title» field of the returned JSON). Default: 'Message sent successfully'.
 * @param $result_titleFail {string} — The title that will be returned if the letter sending is failed somehow (the «title» field of the returned JSON). Default: 'Unexpected error =('.
 * @param $result_messageSuccess {string} — The message that will be returned if the letter sending is successful (the «message» field of the returned JSON). Default: 'We will contact you later.'.
 * @param $result_messageFail {string} — The message that will be returned if the letter sending is failed somehow (the «message» field of the returned JSON). Default: 'Something happened while sending the message.<br />Please try again later.'.
 * 
 * @link http://code.divandesign.biz/modx/ddsendfeedback/1.9.2
 * 
 * @copyright 2010–2016 DivanDesign {@link http://www.DivanDesign.biz }
 */

//Подключаем MODX.ddTools
require_once $modx->getConfig('base_path').'assets/libs/ddTools/modx.ddtools.class.php';

//Для обратной совместимости
extract(ddTools::verifyRenamedParams($params, array(
	'email_docField' => array('docField', 'getEmail'),
	'email_docId' => array('docId', 'getId'),
	'from_formField' => 'fromField',
	'result_titleSuccess' => 'titleTrue',
	'result_titleFail' => 'titleFalse',
	'result_messageSuccess' => 'msgTrue',
	'result_messageFail' => 'msgFalse'
)));

//Если задано имя поля почты, которое необходимо получить
if (isset($email_docField)){
	$email = ddTools::getTemplateVarOutput(array($email_docField), $email_docId);
	$email = $email[$email_docField];
}

//Если всё хорошо
if (
	(isset($tpl) || isset($text)) &&
	isset($email) &&
	($email != '')
){
	//Получаем язык админки
	$lang = $modx->getConfig('manager_language');
	
	//Если язык русский
	if(
		$lang == 'russian-UTF8' ||
		$lang == 'russian'
	){
		$result_titleSuccess = isset($result_titleSuccess) ? $result_titleSuccess : 'Заявка успешно отправлена';
		$result_titleFail = isset($result_titleFail) ? $result_titleFail : 'Непредвиденная ошибка =(';
		$result_messageSuccess = isset($result_messageSuccess) ? $result_messageSuccess : 'Наш специалист свяжется с вами в ближайшее время.';
		$result_messageFail = isset($result_messageFail) ? $result_messageFail : 'Во время отправки заявки что-то произошло.<br />Пожалуйста, попробуйте чуть позже.';
		$subject = isset($subject) ? $subject : 'Обратная связь';
	}else{
		$result_titleSuccess = isset($result_titleSuccess) ? $result_titleSuccess : 'Message sent successfully';
		$result_titleFail = isset($result_titleFail) ? $result_titleFail : 'Unexpected error =(';
		$result_messageSuccess = isset($result_messageSuccess) ? $result_messageSuccess : 'We will contact you later.';
		$result_messageFail = isset($result_messageFail) ? $result_messageFail : 'Something happened while sending the message.<br />Please try again later.';
		$subject = isset($subject) ? $subject : 'Feedback';
	}
	
	$titles = array($result_titleFail, $result_titleSuccess);
	$messages = array($result_messageFail, $result_messageSuccess);
	
	$from = isset($from) ? $from : 'info@divandesign.biz';
	
	//Проверяем нужно ли брать имя отправителя из поста
	if (
		isset($from_formField) &&
		$_POST[$from_formField] != ''
	){
		$from = $_POST[$from_formField];
	}
	
	//Проверяем передан ли текст сообщения
	if (!isset($text)){
		$param = array();
		
		//Перебираем пост, записываем в массив значения полей
		foreach ($_POST as $key => $val){
			//Если это строка или число (может быть массив, например, в случае с файлами)
			if (
				is_string($_POST[$key]) ||
				is_numeric($_POST[$key])
			){
				$param[$key] = nl2br($_POST[$key]);
			}
		}
		
		//Добавим адрес страницы, с которой пришёл запрос
		$param['docId'] = ddTools::getDocumentIdByUrl($_SERVER['HTTP_REFERER']);
		$text = ddTools::parseSource($modx->parseChunk($tpl, $param, '[+', '+]'));
	}
	
	//Отправляем письмо
	$sendMailResult = ddTools::sendMail(explode(',', $email), $text, $from, $subject, explode(',', $filesFields));
	
	//Fail by default
	$result_status = 0;
	
	//Перебираем все статусы отправки
	foreach ($sendMailResult as $sendMailResult_item){
		//Запоминаем
		$result_status = $sendMailResult_item;
		
		//Если не отправлось хоть на один адрес, считаем, что всё плохо
		if ($result_status == 0){
			break;
		}
	}
	
	return json_encode(array(
		'status' => (bool) $result_status,
		'title' => $titles[$result_status],
		'message' => $messages[$result_status]
	));
}
?>