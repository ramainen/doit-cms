<?php
function main()
{

	if(substr($_SERVER['REQUEST_URI'],-5)=='index' && !$_GET){
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.substr($_SERVER['REQUEST_URI'],0,-5));
		exit;
	}
	
	
	d()->mail->setSubject('Письмо с сайта');
	d()->mail->setBody('test', 'text/html');
 

	d()->mail->setFrom(array('noreply@mailer.doit-cms.ru' => 'Система оповещения'));

	d()->mail->setTo('ainu.sky@gmail.com');
	d()->mail->send();
	
	
	d()->content = d()->content();
	print d()->render('main_tpl');
}

function hello_world()
{
	print "Hello, World!";
}
