<?php
 

class SocketIO extends UniversalSingletoneHelper
{
	public $url = '';// = http://doit-cms.ru:32800
	public $userid;// = md5(session_id());
	function emit($userid, $event, $data=array()){
		file_get_contents($this->url . '/emit?id='. $userid .'&message='. $event);
	}
}