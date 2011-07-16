<?php
//модель wiki. Содержит функции добавления, вывода отдельной статьи, вспомогательные функции для работы с текстом (Jevix), 
function wiki_default()
{
	global $prefix;
	//Опрелеоение закголовка страницы
	$page=  substr(urldecode($_SERVER['REQUEST_URI']),6); //FIXME использовать API получения адресов
	$page=str_replace('_',' ',$page);
	
	//Получение даных из базы данных
	$result=mysql_query("select * from ".doit()->table." where title LIKE '$page' limit 1");
	if( $line = mysql_fetch_array($result)) {
		print doit()->wiki_default_tpl($line);
	}else{
		print "Страница не найдена";
	}
}