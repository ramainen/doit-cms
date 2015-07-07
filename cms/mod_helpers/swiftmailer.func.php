<?php

d()->singleton('mail',  function(){
	return new DoitMessage();
});

/*
Использование:
d()->mail->setBody("привет какдила", 'text/html');
d()->mail->setTo('ainu.sky@gmail.com');
d()->mail->send();


*/