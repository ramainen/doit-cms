<?php
class SocketIOController
{
	public $url = '';// Можно использовать http://doit-cms.ru:32800
	public $userid;// По умолчанию используется md5(session_id());
	function init($arr = array())
	{
		d()->SocketIO->url = $arr[0];
		$this->url = $arr[0]; //Можно использовать http://doit-cms.ru:32800
		if(!isset($arr[1])){
			$arr[1]=md5(session_id());
		}
		d()->SocketIO->userid = $arr[1];
		$this->userid = $arr[1]; //По умолчанию используется md5(session_id());
		
		print '<script src="/cms/external/socket.io.js"></script>';
		print '<script>var socket = io("' . $this->url . '");socket.emit("register", { userid: "' .  $this->userid  . '" });</script>';
		
	}

}