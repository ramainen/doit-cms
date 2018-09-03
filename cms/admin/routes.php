<?php

/*
DoIt! CMS and VarVar framework
The MIT License (MIT)

Copyright (c) 2011-2018 Damir Fakhrutdinov

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

//die('В процессе разработки. Если вы видите это сообщение, установите $_ENV[\'DOIT_ADMIN_VERSION\']=\'1\' или уберите это значение.');


d()->datapool['urls'][]=array('/admin', 'main', 'admin_root_route');
d()->datapool['urls'][]=array('/admin/login', 'main', 'admin_login_route');
d()->datapool['urls'][]=array('/admin/logout', 'main', 'admin_logout_route');

d()->admin_root_route = function(){
	//
	if(!iam()){
		print d()->view->render('/cms/admin/templates/login.html');
		exit;
	}
	d()->content = d()->content();
	return d()->view->partial('/cms/admin/templates/main.html');
};
//Авторизация
d()->admin_login_route = function(){
	if(!AJAX){
		header('Location: /admin');
		exit;
	}
	
	if(d()->validate('/admin/login')){
		if(!is_array(d()->admin['editor']['login']) && !is_array(d()->admin['editor']['password'] )){
			d()->datapool['admin']['editor']['login']=array(d()->admin['editor']['login'],array());
			d()->datapool['admin']['editor']['password']=array(d()->admin['editor']['password'],array());
		}
		foreach(d()->admin['editor']['login'] as $key=>$value){
			$login=d()->admin['editor']['login'][$key];
			$password=d()->admin['editor']['password'][$key];
			
			if($login == $_POST['login'] && $password === md5($_POST['password'])) {
				$_SESSION['admin']=$_POST['login'];
				unset($_SESSION['is_additional_admin']);
				print 'if(location.pathname== "/admin") { location.href= "/";} else {location=location};';
				exit();
			}
		}
		if($_POST['login']!='admin' && $_POST['login']!='developer' && $_POST['login']!='' && $_POST['login']!='developer' && isset(d()->admin['users']) && isset(d()->admin['users']['enabled']) && d()->admin['users']['enabled']=='yes'){
			$user = d()->Admin_user->where('login = ? and password = ?',$_POST['login'],md5($_POST['password']));
			if(!$user->is_empty){
				$_SESSION['admin']=$_POST['login'];
				$_SESSION['is_additional_admin']=true;
				print 'if(location.pathname== "/admin") {location.href= "/";} else {location=location;};';
				exit();
			}
		}
	}
	print "$('.js-alert').show();";
	exit;
};
d()->admin_logout_route = function(){
	unset($_SESSION['is_additional_admin']);
	unset($_SESSION['admin']);
	header('Location: /');
	exit;
};

d()->route('/admin/var_dump', function(){
	header('Content-type: text/plain; Charset=utf-8');
	var_dump($_FILES);
	var_dump($_POST);
	exit;
});
d()->route('/admin', function(){
	
	
	
});