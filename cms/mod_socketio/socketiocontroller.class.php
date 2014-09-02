<?php
class SocketIOController
{
	public $url = '';// Можно использовать http://cloud.doit-cms.ru
	public $userid;// По умолчанию используется md5(session_id());
	function init($arr = array())
	{
		if(!isset($arr[0])){
			$arr[0]=d()->SocketIO->url;
		}
		d()->SocketIO->url = $arr[0];
		$this->url = $arr[0]; //Можно использовать http://cloud.doit-cms.ru
		if(!isset($arr[1])){
			$arr[1]=d()->SocketIO->userid;
		}
		d()->SocketIO->userid = $arr[1];
		$this->userid = $arr[1]; //По умолчанию используется md5(session_id());
		
		print '<script src="/cms/external/socket.io.js"></script>';
		print '<script>var socket = io("' . $this->url . '");socket.emit("register", { userid: "' .  $this->userid  . '" });</script>';
		
	}

}