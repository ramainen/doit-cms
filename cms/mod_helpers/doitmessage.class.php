<?php
require_once(__DIR__. '/../vendor/Swift/lib/swift_init.php');
//spl_autoload_register(array('Swift', 'autoload'),false,true);
class DoitMessage extends \Swift_Message
{
	
	function send($transport=false){
		if($transport===false){
			$transport = \Swift_MailTransport::newInstance();
		}
		return (\Swift_Mailer::newInstance($transport)->send($this));
		
	}
 
}
