<?php

/**
 *
 * Проверяет, авторизован ли администратор сайта.
 *
 * @param string $username Имя пользователя
 * @return boolean true, если авторизован
 */
function iam($username=false)
{
	if($username===false){
		return isset($_SESSION['admin']);
	}
	
	return isset($_SESSION['admin']) && $_SESSION['admin'] == $username;
}

function ican($what=false){
	return true;
}

