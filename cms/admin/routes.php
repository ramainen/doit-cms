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
d()->route('/admin/edit/:table/:id', function($table,$id){
	
	return d()->view->partial('/cms/admin/templates/edit.html');
	
});
d()->route('/admin', function(){
	
	return d()->view->partial('/cms/admin/templates/empty.html');
	
});




d()->route('/admin/tinymce-file-upload', function(){
	if (isset($_FILES['image']) || isset($_FILES['file']) || !empty($_POST['base64'])) {
		$our_folder = $_SERVER["DOCUMENT_ROOT"]."/storage";
		$result = array();
		if (isset($_FILES['image']) || isset($_FILES['file']) || !empty($_POST['base64'])) {
			$is_tinymce_handler = false;
			if(!empty($_POST['base64'])){
				$file = '/storage/tmp_base64.png';
				list($type, $data) = explode(';', $_POST['base64']);
				list(, $data)      = explode(',', $data);
				$data = base64_decode($data);
				$ext = '.png';
				$file = $_SERVER['DOCUMENT_ROOT'].'/storage/tmp_base64.png';
				file_put_contents($file, $data);
				$filename = 'blob.png';
				$is_magic = true;
			}elseif (isset($_FILES['image']) ){
				$file = $_FILES['image']['tmp_name'];
				$filename = $_FILES['image']['name'];
				$ext = mb_strtolower(strrchr($_FILES['image']['name'],'.'));
				$is_magic = false;
			}else if (isset($_FILES['file'])) {
				$is_tinymce_handler = true;
				$file = $_FILES['file']['tmp_name'];
				$ext = mb_strtolower(strrchr($_FILES['file']['name'],'.'));
				$filename =  (md5_file($_FILES['file']['tmp_name'])). $ext;
				$is_magic = false;
			}
			$error = false;
			$size = false;
			if(!in_array($ext, array('.png','.jpg','.jpeg','.gif'))){
				$error = 'Неверное расширение файла';
				$is_uploaded = false;
			}else{
				$is_uploaded =   ( !empty($_POST['base64']) || is_uploaded_file($file));
			}
			if (!$is_uploaded  ) {
				$error = 'Размер файла слишком велик';
			} else if (!$error && !($size = getimagesize($file) ) ) {
				$error = 'Ошибка, неверный тип файла';
			} else if (!$error && !in_array($size[2], array(1, 2, 3, 7, 8) ) ) {
				$error = 'Ошибка типа файла, рекомендуется загружать файлы JPEG';
			} else if (!$error && ($size[0] < 1) || ($size[1] < 1)) {
				$error = 'Размер файла слишком маленький';
			}
			if ($error) {
				$result['result'] = 'failed';
				$result['error'] = $error;
			} else {
				$ext = mb_strtolower(end(explode(".", $filename)), 'UTF-8' );
				$name = tinymce_file_upload_findname($file,$our_folder,$filename,$is_magic);
				$source = $our_folder.'/'.$name.'.'.$ext;

				if(!copy($file, $source)) {
					$result['result'] = 'error';
					$result['error'] = 'Ошибка при копировании файла!';
				} else {
					$result['result'] = 'success';
					$newadress="/storage";
					$newfilename= $name.'.'.$ext;
					if(isset ($_GET['uri']))$newadress=$_GET['uri'];
					if($newadress=="") {
						$newadress="/storage";
					}
					$result['filename'] = $newadress."/".$newfilename;
					$result['size'] = "Загружено изображение ({$size['mime']}) размером {$size[0]}px/{$size[1]}px.";
				}
			}
		} else {
			$result['result'] = 'error';
			if($_GET['lng']=='ru') {
				$result['error'] = 'Нет файла или внутренняя ошибка!';
			} else {
				$result['error'] = 'No file or an internal error!';
			}
		}
		
		$return=array();
		foreach ($result as $key=>$val) {
			$return[$key] =   $val ;
		}
		if($is_tinymce_handler ){
			print json_encode(array('location'=>$return['filename']));
		}else{
			print '<script>';
			print "window.parent.tmp_tinymce_editor.insertContent('<img src=\"". htmlspecialchars($return['filename']) ."\" alt=\"\" />');";
			print '</script>';
		}
	
	} 
	exit;
	
});








d()->route('/admin/:u*', function(){

	return d()->view->partial('/cms/admin/templates/empty.html');
}); //Костыль для 404