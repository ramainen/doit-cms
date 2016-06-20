<?php

d()->singleton('mail',  function(){
	_swiftmailer_init();
	$message = new DoitMessage();
	$message->setFrom (array($_SERVER['SERVER_ADMIN'] => $_SERVER['SERVER_ADMIN']));
	return $message;
});

/*
Использование:
d()->mail->setBody("привет какдила", 'text/html');
d()->mail->setTo('ainu.sky@gmail.com');
d()->mail->send();


*/