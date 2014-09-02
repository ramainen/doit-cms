<?php


/*
TODO: сервер Массовая отправка SocketIO->emit(array(user1,user2,user3))
TODO: сервер Поддержка групп (SocketIO->register_group('group_id'[, user_id])
*/

class SocketIO extends UniversalSingletoneHelper
{
	public $url;// Облачный сервер по умолчанию
	public $userid;// = md5(session_id());
	
	
	function __construct()
	{
		//Установка значений по умолчанию
		$this->url = 'http://cloud.doit-cms.ru'; // Облачный сервер по умолчанию
		$this->userid  = md5(session_id());
	}
	
	/**
	* Генерация запроса к серверу по чистому GET запросу; урезанный вариант
	*/
	function emit_get($userid, $event, $data=array()){
		
		if (is_string($data)){
			$string = $data;
			$data=array();
			$data['_type'] = 'string';
			$data['_data'] = $string;
		}
		if(is_array($userid)){
			$userid = $userid[0];
		}
		$data['id'] = $userid;
		$data['message'] = $event;
		
		
		return file_get_contents($this->url . '/emit?'. http_build_query($data));
	}
	
	/**
	* Более гибкая отправка по POST запросу без ограничений по структуре данных
	*/
	function emit($userid, $event, $data=array()){	
		if( function_exists("curl_init") && $curl = curl_init() ) {
			curl_setopt($curl, CURLOPT_URL, $this->url.'/emit');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			
			$post_data=array();
			$post_data['id'] = $userid;
			$post_data['message'] = $event;
			$post_data['data'] = json_encode($data);
			
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
			$out = curl_exec($curl);
			curl_close($curl);
			return $out;
		}else{
			//fallback, если остальное не работает
			return $this->emit_get($userid, $event, $data);
		}
	}
}