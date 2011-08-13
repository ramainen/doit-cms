<?php

function edit($params){
		
	if(!isset($_SESSION['admin'])) {
		return ""; //Проверка на права администратора
	}
		

	print '<a href="/admin/edit/'.$params[0]->table.'/'.$params[0]->id.'" target="_blank" ><img style="border:none;" src="/cms/internal/gfx/edit.png"></a>';
}

//Функция возвращает массив возможных полей
function adminFields()
{
	
	doit()->load_and_parse_ini_file('app/fields/'.url(3).'.ini');
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
