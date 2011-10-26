<?php
//Помощники
function edit($params=false){
    if($params===false) {
        $params=array(d()->this);
    }
	if(!is_array($params)) {
		$params=array($params);	
	}
		
	if(!isset($_SESSION['admin'])) {
		return ""; //Проверка на права администратора
	}
		

	print '<a href="/admin/edit/'.$params[0]->table.'/'.$params[0]->id.'" target="_blank" ><img style="border:none;" src="/cms/internal/gfx/edit.png"></a>';
}

function delete($params=false){
    if($params===false) {
        $params=array(d()->this);
    }
    
	if(!is_array($params)) {
		$params=array($params);	
	}
		
	if(!isset($_SESSION['admin'])) {
		return ""; //Проверка на права администратора
	}
		

	print '<a href="/admin/delete/'.$params[0]->table.'/'.$params[0]->id.'" target="_blank" ><img style="border:none;" src="/cms/internal/gfx/delete.png"></a>';
}

function add($params){
 
	if(!isset($_SESSION['admin'])) {
		return ""; //Проверка на права администратора
	}
	
	if(!is_array($params)) {
		$params=array($params);	
	}
		
	$params_string='';

	foreach($params as $key=>$value){
		if(!is_numeric($key)) {
			if ($params_string=='') {
				$params_string='?';
			} else {
				$params_string.='&';
			}
			$params_string.= $key.'='.$value;
		}
	}
	
	print '<a href="/admin/edit/'.$params[0].'/add'.$params_string.'" target="_blank" ><img style="border:none;" src="/cms/internal/gfx/add.png"></a>';
}

function admin_list()
{
	d()->load_and_parse_ini_file('app/fields/'.url(3).'.ini');
	
	if (url(4)=='') {
		//list/goods     просто список всех полей
		$query='select * from '.e(url(3));
		d()->list_addbutton='';
		d()->list_addbutton.='<a class="admin_button" href="/admin/edit/'. url(3) .'/add">Добавить</a>';
		
		if(isset(d()->admin['bottombuttons'])) {
		$bottombuttons=d()->admin['bottombuttons'];

		foreach($bottombuttons as $bottombutton) {
			d()->list_addbutton.=' <a class="admin_button" href="/admin'.$bottombutton[0].'">'.$bottombutton[1].'</a>';
		}
	}
		
		
	} else {
		if(url(5) == '') {
			if(url(4)=='index') {
				//list/goods/    список полей с goods_id = NULL
				$query='select * from `'.e(url(3)).'` where `'.e(to_o(url(3))).'_id` is NULL';
				d()->list_addbutton='<a class="admin_button" href="/admin/edit/'. url(3) .'/add">Добавить</a>';
			} else {
				//list/goods/4    список полей с goods_id = 4
				if(is_numeric(url(4))) {
					$query='select * from `'.e(url(3)).'` where `'.e(to_o(url(3)))."_id` = '".e(url(4))."' ";
					d()->list_addbutton='<a class="admin_button" href="/admin/edit/'. h(url(3)) .'/add?'.h(to_o(url(3))).'_id='.h(url(4)).'">Добавить</a>';
				}else{
					$query='select * from `'.e(url(3)).'` where `'.e(to_o(url(3))).'_id` IN (select id from `'.e(url(3))."` where `url` = '".e(url(4))."') ";
					d()->list_addbutton=' ';
				}

			}
		} else {
			//list/goods/catalog_id/4             список полей с catalog_id = 4
			$query='select * from `'.e(url(3)).'` where `'.e(url(4))."` = '".e(url(5))."' ";
			d()->list_addbutton='<a class="admin_button" href="/admin/edit/'. h(url(3)) .'/add?'.e(url(4)).'='.h(url(5)).'">Добавить</a>';
		}
	}
	print '<!-- '.$query.' -->';
	//Определение дополнительных кнопок
	
	 

	$addbuttons = array();
	
	if(isset(d()->admin['addbuttons'])) {
		$addbuttons=d()->admin['addbuttons'];
	}
	
	$result=mysql_query($query);
	$data=array();
	while ($line=mysql_fetch_array($result)) {
		$line['addbuttons']='';
		foreach($addbuttons as $key=>$value) {
			$line['addbuttons'] .= '<a href="/admin'.  $value[0] . $line['id'] . '" class="admin_button">'.$value[1].'</a> ';
		}
		$data[]=$line;
	}
	d()->objectrow = $data;
	print d()->admin_objectlist();
}

//	Основная функция редактирования, которая получает данные, выводит форму, обрабатывает действия, перезагружает страницу
function admin_edit()
{
	print action('admin_save_data');
	$rows=array();
	$tableortype = url(3);
	if (url(4)!='add') {
		//TODO: db()->sql();
		if (!($line=mysql_fetch_array(mysql_query("select * from `".mysql_real_escape_string(url(3))."` where `id` = '".mysql_real_escape_string(url(4))."'")))) {
			$line=array();
		}
	} else {
		$line=array();
	}
	if(isset($line['type']) && $line['type']!='') {
		$tableortype = to_p($line['type']);
	}
	
	if(isset($_GET['type']) && $_GET['type']!='') {
		$tableortype = to_p($_GET['type']);
	}
	$fields=d()->admin_get_fields($tableortype);
	//список элементов, для которых переопределелили скрытые параметры
	//при помощи GET. Если их нет, то создаются новые скрытые е параметры.
	$setted_flag=array();
	
	foreach ($fields as $field) {
		d()->title=$field['title'];
		d()->name='data['.$field['name'].']';
		$setted_flag[$field['name']]=true;
		d()->value='';
		if (url(4)=='add' && isset($_GET[$field['name']])) {
			d()->value=$_GET[$field['name']];
		}
		if (isset($line[$field['name']])) {
			d()->value=$line[$field['name']];
		}
		$rows[]=d()->call('admin_'.$field['type']);
	}	
	
	if(url(4)=='add') {
		//Установка скрытых полей
		foreach($_GET as $key=>$value) {
			if (!isset($setted_flag[$key])) {
				d()->name = 'data['.$key.']';		
				d()->value = $value;
				$rows[]=d()->call('admin_hidden');
			}
		}
	}
	
	d()->tabletitle = 'Редактирование элемента';
	if(url(4)=='add') {
		d()->tabletitle = 'Добавление нового элемента';
	}
	d()->tablerow = $rows;
	print d()->admin_edit_table(); //Эту функцию можно переопределять
}

function admin_save_data($params)
{
	//TODO: Новое API для добавление новых элементов в базу данных;  
	$elemid=url(4);
	if($elemid=='add') {
		//Добавление элементов - делаем малой кровью - предварительно создаём строку в таблице
		$result=mysql_query("insert into `".mysql_real_escape_string(url(3))."`  () values ()");
		$elemid=mysql_insert_id();
	}
	//FIXME: костыль
	if(isset($params['url'])){
		if($params['url']=='') {
			$params['url']='page'.$elemid;
		}

		if(substr($params['url'],0,1)=='/') {
			$params['url']=substr($params['url'],1);
		}
		
		$params['url']=str_replace('/','_',$params['url']);
	}
    $result_str="update `".url(3)."` set  ";
    $i=0;
	
	
	foreach($params as $key=>$value) {
		$i++;
		if (substr($key,-3)=='_id' && $value == '') {
			$result_str.=" `" . $key . "`= NULL ";
		} else {
			$result_str.=" `" . $key . "`= '".mysql_real_escape_string($value)."' ";
		}
        if ($i<count($params)) $result_str.=' , ';
    }
		
    $result_str.=" where `id`=".mysql_real_escape_string($elemid);
	
    mysql_query($result_str);
	if($_POST['admin_command_redirect_close']=='yes') {
		return  "<script> window.opener.document.location.href=window.opener.document.location.href;window.open('','_self','');window.close();</script>";
	}else{
		header('Location: /admin/list/'.url(3));
		exit();
	}

}


function admin_delete()
{
	print action('admin_delete_element');
	print d()->admin_delete_tpl(); 
}
function admin_delete_element($params)
{
	$result=mysql_query("delete from `".e(url(3))."`  where id='".e(url(4))."'");
	return  "<script> window.opener.document.location.href=window.opener.document.location.href;window.open('','_self','');window.close();</script>";
}

//Функция возвращает массив возможных полей
function admin_get_fields($tableortype='')
{
	$data=array();
	if ($tableortype=='') {
		$tableortype=url(3);
	}
	
	d()->load_and_parse_ini_file('app/fields/'.$tableortype.'.ini');
	$rows = doit()->admin['fields'];
	foreach ($rows as $key=>$value) {
		$data[]=array('name'=>$value[1],'type'=>$value[0],'title'=>$value[2]);
	}
    return $data;
}

//Открытие шаблона либо вывод формы авторизации
function admin()
{

	//TODO: переписать на валидаторах
	if(isset($_POST['action']) && $_POST['action']=='admin_login'){
		if(d()->admin['editor']['login'] == $_POST['login'] && d()->admin['editor']['password'] == md5($_POST['password'])) {
			$_SESSION['admin']=$_POST['login'];
			header('Location: /');
			exit();
		}
		d()->notice='Неверный логин или пароль';
	}

	if(!isset($_SESSION['admin'])) {
		return d()->admin_authorisation();
	}
	d()->content = d()->content();
	return d()->render('admin_tpl');
}
