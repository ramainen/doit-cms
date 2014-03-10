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
		if(!iam('admin') && !iam('developer')){
			//До этого не должно дойти
			exit();
		}
		
		if(d()->validate()){
			if($_POST['element_id']=='add'){
				if(empty(d()->params['password'])){
					d()->add_notice('Вы не ввели пароль','password');
				}
			}
		}
		if(d()->validate()){
			if(d()->params['login']=='admin' || d()->params['login']=='developer'){
				d()->add_notice('Имена admin и developer использовать нельзя.','login');
			}
		}
		if(d()->validate()){
			if($_POST['element_id']=='add'){
				if (! d()->Admin_user->is_empty){
					if(!d()->Admin_user->where('login = ?',d()->params['login'])->is_empty){
						d()->add_notice('Такой логин уже занят','login');
					}
				}
			}else{
				if (! d()->Admin_user->is_empty){
					if(!d()->Admin_user->where('login = ?',d()->params['login'])->is_empty && d()->Admin_user->where('login = ?',d()->params['login'])->id!=$_POST['element_id']){
						d()->add_notice('Такой логин уже занят','login');
					}
				}
			}
				 
			 
		}
		if(d()->validate()){
			if($_POST['element_id']=='add'){
				$user = d()->Admin_user->new;
			}else{
				$user = d()->Admin_user($_POST['element_id']);
				if($user->is_empty){
					d()->add_notice('Такой пользователь не найден');
					print '$(".notice_container").html(' . json_encode(d()->notice(array('bootstrap'))) . '); ';
					d()->reload();
					exit();
					//return 'Пользователь не найден';
				}
			}
			$user->login = d()->params['login'];
			$user->whitelist = d()->params['whitelist'];
			if(d()->params['password']!=''){
				$user->password = md5(d()->params['password']);
			}
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

		if(!iam('admin') && !iam('developer')){
			return 'Только главный администратор может управлять доступом.';
		}
		if(url(4)!='add'){
			d()->user = d()->Admin_user(url(4));
			if(d()->user->is_empty){
				return 'Пользователь не найден';
			}
		}else{
			d()->user = d()->Admin_user->limit(0);
		}
		print d()->view;
	}
}