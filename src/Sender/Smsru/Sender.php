<?php
namespace ddSendFeedback\Sender\Smsru;

class Sender extends \ddSendFeedback\Sender\Sender {
	protected 
		$apiId = '',
		$to = '';
	
	private
		$url = 'https://sms.ru/sms/send?json=1';
	
	/**
	 * send
	 * @version 1.0.2 (2018-03-19)
	 * 
	 * @uses ddMakeHttpRequest >= 1.3.
	 * 
	 * @desc Send sms via sms.ru.
	 * 
	 * @return $result {array} — Returns the array of send status.
	 * @return $result[0] {0|1} — Status.
	 */
	
	public function send(){
		global $modx;
		
		$result = [0 => 0];
		
		//Заполнены ли обязательные параметры
		if(
			//Передали ли api_id
			isset($this->apiId) &&
			//телефон получателя
			isset($this->to) &&
			//и сообщение
			isset($this->text)
		){
			//отсылаем смс
			$requestResult = $modx->runSnippet('ddMakeHttpRequest', [
				'url' => $this->url.'&api_id='.$this->apiId.'&to='.$this->to.'&msg=' .urlencode($this->text),
			]);
			
			//разбиваем пришедшее сообщение
			$requestResult = json_decode($requestResult, true);
			
			if ($requestResult['sms'][$this->to]['status'] == 'OK'){
				$result[0] = 1;
			}else{
				//Если ошибка, то залогируем
				\ddTools::logEvent([
					'message' => '<code><pre>'.print_r($requestResult, true).'</pre></code>',
					'source' => 'ddSendFeedback → Smsru',
					'eventType' => 'error'
				]);
			}
		}
		
		return $result;
	}
}
?>