<?php

//Дополнительные пользователи
class Admin_usersController
{
	function index()
	{
		d()->admin_users_list = d()->Admin_user;
		print d()->view();
	}
	function save()
	{
		if(!iam()){
			//До этого не должно дойти
			exit();
		}
		if(d()->validate()){
			if($_POST['element_id']=='add'){
				$user = d()->Admin_user->new;
			}
			$user->login = d()->params['login'];
			$user->password = md5(d()->params['password']);
			$user->save();

			print 'document.location.href="/admin/list/admin_users";';
			exit();
		}
		if(d()->notice(array('bootstrap'))){
			print '$(".notice_container").html(' . json_encode(d()->notice(array('bootstrap'))) . '); ';
		}
		d()->reload();
	}
	function edit()
	{
		if(url(4)!='add'){
			d()->user = d()->Admin_user(url(4));
		}else{
			d()->user = d()->Admin_user->limit(0);
		}
		print d()->view;
	}
}