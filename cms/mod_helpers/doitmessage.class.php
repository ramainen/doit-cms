<?php
require_once(__DIR__. '/../vendor/Swift/lib/swift_init.php');
//spl_autoload_register(array('Swift', 'autoload'),false,true);
class DoitMessage extends \Swift_Message
{
	protected $current_transport = false;
	
	function setTransport($transport=false){
		if($transport===false){
			$transport = \Swift_MailTransport::newInstance();
		}
		$this->current_transport = $transport;
	}
	
	function send(){
		if($this->current_transport===false){
			$this->current_transport = \Swift_MailTransport::newInstance();
		}
		return (\Swift_Mailer::newInstance($this->current_transport)->send($this));
		
	}
 
}
