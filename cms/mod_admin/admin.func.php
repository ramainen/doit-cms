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
/**
 *
 * Проверяет, авторизован ли администратор сайта.
 *
 * @return boolean
 */
function iam()
{
	if(isset($_SESSION['admin'])) {
		return true;
	}
	return false;
}
function admin_logout()
{
	unset($_SESSION['admin']);
	header('Location: /');
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

function admin_show()
{
	unset (d()->datapool['admin']['bottombuttons']);
	unset (d()->datapool['admin']['addbuttons']);
	unset (d()->datapool['admin']['show']);
	d()->load_and_parse_ini_file('app/fields/'.url(3).'.ini');
	if(!empty(d()->admin['show'])){
		foreach(d()->admin['show'] as $value){

			$url=$value[0];
			if(substr($url,-1)=='/'){
				$url .= url(4);
			}
			$subarray=explode('/', $url);
			if(!isset($subarray[1])){
				$subarray[1]='';
			}
			if(!isset($subarray[2])){
				$subarray[2]='';
			}
			d()->curr_title=$value[1];
			print d()->admin_show_one_list($subarray[0],$subarray[1],$subarray[2]);
		}
	}

}
function admin_show_one_list($table,$id1,$id2)
{
	unset (d()->datapool['admin']['bottombuttons']);
	unset (d()->datapool['admin']['addbuttons']);
	d()->curr_table=$table;
	d()->load_and_parse_ini_file('app/fields/'.$table.'.ini');

	if ($id1=='') {
		//list/goods     просто список всех полей
		$query='select * from '.e($table).'   order by `sort`';
		d()->list_addbutton='';
		d()->list_addbutton.='<a class="admin_button" href="/admin/edit/'. $table .'/add">Добавить</a>';

		if(isset(d()->admin['bottombuttons'])) {
		$bottombuttons=d()->admin['bottombuttons'];

		foreach($bottombuttons as $bottombutton) {
			d()->list_addbutton.=' <a class="admin_button" href="/admin'.$bottombutton[0].'">'.$bottombutton[1].'</a>';
		}
	}


	} else {
		if($id2 == '') {
			if($id1=='index') {
				//list/goods/    список полей с goods_id = NULL
				$query='select * from `'.e($table).'` where `'.e(to_o($table)).'_id` is NULL  order by `sort`';
				d()->list_addbutton='<a class="admin_button" href="/admin/edit/'. $table .'/add">Добавить</a>';
			} else {
				//list/goods/4    список полей с goods_id = 4
				if(is_numeric($id1)) {
					$query='select * from `'.e($table).'` where `'.e(to_o($table))."_id` = '".e($id1)."' order by `sort`";
					d()->list_addbutton='<a class="admin_button" href="/admin/edit/'. h($table) .'/add?'.h(to_o($table)).'_id='.h($id1).'">Добавить</a>';
				}else{
					$query='select * from `'.e($table).'` where `'.e(to_o($table)).'_id` IN (select id from `'.e($table)."` where `url` = '".e($id1)."')  order by `sort`";
					d()->list_addbutton=' ';
				}

			}
		} else {
			//list/goods/catalog_id/4             список полей с catalog_id = 4
			$query='select * from `'.e($table).'` where `'.e($id1)."` = '".e($id2)."'  order by `sort`";
			d()->list_addbutton='<a class="admin_button" href="/admin/edit/'. h($table) .'/add?'.e($id1).'='.h($id2).'">Добавить</a>';
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
	if(mysql_errno()!=0){
		print mysql_error(); //Отладка
	}else{
		while ($line=mysql_fetch_array($result)) {
			$line['addbuttons']='';
			foreach($addbuttons as $key=>$value) {
				$line['addbuttons'] .= '<a href="/admin'.  $value[0] . $line['id'] . '" class="admin_button">'.$value[1].'</a> ';
			}
			if (empty($line['sort'])) {
				//ВНЕЗАПНО сортировка пустая
				mysql_query('UPDATE  `'.e($table).'` set `sort` = `id` where `id` = '.e($line['id']));
			}
			$data[]=$line;
		}
	}
	d()->objectrow = $data;
	
	if(empty($_GET['sort'])){
		print d()->view();
	}else{
		
		if(d()->validate('admin_do_sort')){
			$url = preg_replace('/\?.*/','',$_SERVER['REQUEST_URI']);
			$oldlist=d()->objectrow;
			$newlist=d()->params['elements'];
			
			foreach ($newlist as $key=>$value){
				if($oldlist[$key]['id']*1 != $value*1){
					// Элементу с ID = $value присваеваем новый SORT, тот которы йбыл под номером $key ( $oldlist[$key]['sort'] ) 
					mysql_query('UPDATE  `'.e($table).'` set `sort` = '.e($oldlist[$key]['sort']).' WHERE `id` = '.e($value)).'';
				}
			}			 
			header('Location: '.$url) ;
			exit();
		}
		print d()->admin_show_one_sortable_list();		
	}

}
function admin_list()
{
	d()->curr_title='Список объектов из таблицы '.url(3);
	print d()->admin_show_one_list(url(3),url(4),url(5));
	
	print d()->admin_show();
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
		d()->field_params=$field['all'];
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
	print d()->view(); //Эту функцию можно переопределять
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
			$params['url']=to_o(url(3)).$elemid;
		}

		if(substr($params['url'],0,1)=='/') {
			$params['url']=substr($params['url'],1);
		}
		
		$params['url']=str_replace('/','_',$params['url']);
	}
	$params['sort']=$elemid;
    $result_str="update `".mysql_real_escape_string(url(3))."` set  ";
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

 
	$not_reqursy=0;
	while(!mysql_query($result_str) && 1054 == mysql_errno()) {
		$error_string=mysql_error();
		$not_reqursy++;
		if($not_reqursy>30) {
			print "Произошла ошибка рекурсии. Пожалуйста, добавьте поля вручную - у меня не получилось. Спасибо.";
			exit();
		}
		foreach($params as $key=>$value) {
			if(strpos($error_string , "'".$key."'")!==false){
				if (substr($key,-3)=='_id') {
					$result = mysql_query("ALTER TABLE `".mysql_real_escape_string(url(3))."` ADD COLUMN `$key` int NULL" );
				} else {
					$result = mysql_query("ALTER TABLE `".mysql_real_escape_string(url(3))."` ADD COLUMN `$key` text NULL, DEFAULT CHARACTER SET=utf8" );
				}
				
			}
		}
	}
	
	if($_POST['admin_command_redirect_close']=='yes') {
		return  "<script> window.opener.document.location.href=window.opener.document.location.href;window.open('','_self','');window.close();</script>";
	}else{

		header('Location: '.$_POST['_http_referer']);
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
		$data[]=array('name'=>$value[1],'type'=>$value[0],'title'=>$value[2],'all'=>$value);
	}
    return $data;
}

//Открытие шаблона либо вывод формы авторизации
function admin()
{

	//TODO: переписать на валидаторах
	if(isset($_POST['action']) && $_POST['action']=='admin_login'){
		if(!is_array(d()->admin['editor']['login']) && !is_array(d()->admin['editor']['password'] )){
			d()->admin['editor']['login']=array(d()->admin['editor']['login']);
			d()->admin['editor']['password']=array(d()->admin['editor']['password']);
		}
		foreach(d()->admin['editor']['login'] as $key=>$value){
			$login=d()->admin['editor']['login'][$key];
			$password=d()->admin['editor']['password'][$key];
			
			if($login == $_POST['login'] && $password == md5($_POST['password'])) {
				$_SESSION['admin']=$_POST['login'];
				header('Location: /');
				exit();
			}
		}
		
		d()->notice='Неверный логин или пароль';
	}

	if(!isset($_SESSION['admin'])) {
		return d()->admin_authorisation();
	}
	d()->content = d()->content();
	return d()->render('admin_tpl');
}
