<?php

//Функция возвращает массив возможных полей
function adminFields()
{
	
	global  $_URL;
	doit()->loadAndParseIniFile('tpl/fields/'.$_URL[2].'.ini');
    $_rows = doit()->admin['fields'];
 	foreach ($_rows as $_key=>$_value)
	{	
		$_a=array();
		$data[]=array('name'=>$_value[0],'type'=>$_value[1],'title'=>$_value[2]);
	}
    return $data; // все фрагменты делают return вместо print, поэтому можно передавать данные любого типа. Вывод обрезается.
}

//Открытие шаблона либо вывод формы авторизации
function admin()
{
	if(isset($_POST['action']) && $_POST['action']=='admin_login'){
		if(doit()->admin['editor']['login'] == $_POST['login'] && doit()->admin['editor']['password'] == md5($_POST['password'])) {
			$_SESSION['admin']=$_POST['login'];
			header('Location: /');
			exit();
		}
		doit()->notice='Неверный логин или пароль';
	}

	if(!isset($_SESSION['admin'])) {
		return doit()->adminform();
	}
	return doit()->admin_tpl();
}
