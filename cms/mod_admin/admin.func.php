<?php
//Помощники
function edit($params=false){
    if($params===false) {
        $params=array(d()->this);
    }
	if(!is_array($params)) {
		$params=array($params);	
	}
		
	$attr='';
	if(isset($params['style'])) {
		$attr .= ' style="'.$params['style'].'" ';
	}
	if(isset($params['class'])) {
		$attr .= ' class="'.$params['class'].'" ';
	}
	if(isset($params['title'])) {
		$attr .= ' title="'.$params['title'].'" ';
	}
	
	if(!isset($_SESSION['admin'])) {
		return ""; //Проверка на права администратора
	}
		

	print '<a href="/admin/edit/'.$params[0]->table.'/'.$params[0]->id.'" target="_blank" '.$attr.' ><img style="border:none;" src="/cms/internal/gfx/edit.png"></a>';
}

/**
 *
 * Проверяет, авторизован ли администратор сайта.
 *
 * @param string $username Имя пользователя
 * @return boolean true, если авторизован
 */
function iam($username='')
{
	if($username!=''){
		if(isset($_SESSION['admin']) && ($_SESSION['admin'] == $username)) {
			return true;
		}
	}else{
		if(isset($_SESSION['admin'])) {
			return true;
		}
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
	
	$attr='';
	if(isset($params['style'])) {
		$attr .= ' style="'.$params['style'].'" ';
		unset($params['style']);
	}
	if(isset($params['class'])) {
		$attr .= ' class="'.$params['class'].'" ';
		unset($params['class']);
	}
	if(isset($params['title'])) {
		$attr .= ' title="'.$params['title'].'" ';
		unset($params['title']);
	}
	
	if(!isset($_SESSION['admin'])) {
		return ""; //Проверка на права администратора
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
	
	print '<a href="/admin/edit/'.$params[0].'/add'.$params_string.'" '.$attr.' target="_blank" ><img style="border:none;" src="/cms/internal/gfx/add.png"></a>';
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
		$query='select * from '.et($table).'   order by '.DB_FIELD_DEL.'sort'.DB_FIELD_DEL;
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
				$query='select * from '.DB_FIELD_DEL . et($table).DB_FIELD_DEL . ' where '.DB_FIELD_DEL .et(to_o($table)).'_id'.DB_FIELD_DEL.' is NULL  order by '.DB_FIELD_DEL .'sort'.DB_FIELD_DEL;
				d()->list_addbutton='<a class="admin_button" href="/admin/edit/'. $table .'/add">Добавить</a>';
			} else {
				//list/goods/4    список полей с goods_id = 4
				if(is_numeric($id1)) {
					$query='select * from '.DB_FIELD_DEL.et($table).DB_FIELD_DEL.' where '.DB_FIELD_DEL .et(to_o($table))."_id".DB_FIELD_DEL ." = ".e($id1)." order by ".DB_FIELD_DEL. "sort".DB_FIELD_DEL;
					d()->list_addbutton='<a class="admin_button" href="/admin/edit/'. h($table) .'/add?'.h(to_o($table)).'_id='.h($id1).'">Добавить</a>';
				}else{
					$query='select * from '.DB_FIELD_DEL .et($table).DB_FIELD_DEL . ' where '.DB_FIELD_DEL .et(to_o($table)).'_id'.DB_FIELD_DEL . ' IN (select id from '.DB_FIELD_DEL .et($table).DB_FIELD_DEL ." where ".DB_FIELD_DEL."url".DB_FIELD_DEL." = ".e($id1).")  order by ".DB_FIELD_DEL . "sort".DB_FIELD_DEL;
					d()->list_addbutton=' ';
				}

			}
		} else {
			//list/goods/catalog_id/4             список полей с catalog_id = 4
			$query='select * from '.DB_FIELD_DEL.et($table).DB_FIELD_DEL .' where '.DB_FIELD_DEL .et($id1).DB_FIELD_DEL. " = ".e($id2)."  order by ".DB_FIELD_DEL."sort".DB_FIELD_DEL;
			d()->list_addbutton='<a class="admin_button" href="/admin/edit/'. h($table) .'/add?'.et($id1).'='.h($id2).'">Добавить</a>';
		}
	}
	print '<!-- '.$query.' -->';
	//Определение дополнительных кнопок



	$addbuttons = array();

	if(isset(d()->admin['addbuttons'])) {
		$addbuttons=d()->admin['addbuttons'];
	}

	$result=d()->db->query($query);
	$data=array();
	if($result===false){
		$err= d()->db->errorInfo(); //Отладка
		print $err[2];
	}else{
		$all_lines=$result->fetchAll();
		foreach($all_lines as $key0=> $line){
			$all_lines[$key0]['addbuttons']='';
			foreach($addbuttons as $key => $value) {
				$all_lines[$key0]['addbuttons'] .= '<a href="/admin'.  $value[0] . $line['id'] . '" class="admin_button">'.$value[1].'</a> ';
			}
			if (empty($line['sort'])) {
				//ВНЕЗАПНО сортировка пустая
				//TODO: ХЕРОВО
				d()->db->exec('UPDATE  `'.et($table).'` set `sort` = `id` where `id` = '.((int)$line['id']));
			}
		}
		$data = $all_lines;
	}
	
	
	foreach ($data as $key=>$value){
		if($data[$key]['title']=='' && isset($data[$key]['ru_title']) && $data[$key]['ru_title']!=''){
			$data[$key]['title']=$data[$key]['ru_title'];
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
					d()->db->exec('UPDATE  `'.et($table).'` set `sort` = '.((int)$oldlist[$key]['sort']).' WHERE `id` = '.((int)$value)).'';
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
	$scenario=0;
	$tableortype = url(3);
	//Перенаправление
	if(!is_numeric(url(4)) && url(4)!='add'){
			$scenario=1;
	}
	if (url(4)!='add') {
		if($scenario==1){
			$result=d()->db->query("select * from `".et(url(3))."` where `url` = ".e(url(4)));
			if ($result===false ||  ($line=$result->fetch())===false) {
				$scenario=2;
				$_GET['url']=url(4);
				$line=array();
			}
		} else {
			$result=d()->db->query("select * from `".et(url(3))."` where `id` = ".(int)url(4));
			if ($result===false ||  ($line=$result->fetch())===false) {
				$line=array();
			}
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
		if ((url(4)=='add' || $scenario==2) && isset($_GET[$field['name']])) {
			d()->value=$_GET[$field['name']];
		}
		if (isset($line[$field['name']])) {
			d()->value=$line[$field['name']];
		}
		$rows[]=d()->call('admin_'.$field['type']);
	}	
	
	if(url(4)=='add' || $scenario==2) {
		//Установка скрытых полей
		foreach($_GET as $key=>$value) {
			if (!isset($setted_flag[$key])) {
				d()->name = 'data['.$key.']';		
				d()->value = $value;
				$rows[]=d()->call('admin_hidden');
			}
		}
	}
	if($scenario==2){
		d()->name = '_scenario';
		d()->value = 'add';
		$rows[]=d()->call('admin_hidden');
	}
	if($scenario==1){
			d()->name = '_scenario';
			d()->value = 'edit';
			$rows[]=d()->call('admin_hidden');
	}
	d()->tabletitle = 'Редактирование элемента';
	if(url(4)=='add' || $scenario==2) {
		d()->tabletitle = 'Добавление нового элемента';
	}
	d()->tablerow = $rows;
	print d()->view(); //Эту функцию можно переопределять
}

function admin_save_data($params)
{
	//TODO: Новое API для добавление новых элементов в базу данных;  
	$elemid=url(4);
	$scenario=0;
	if(isset($_POST['_scenario']) && $_POST['_scenario']=='add'){
		$scenario=2;
	}
	if(isset($_POST['_scenario']) && $_POST['_scenario']=='edit'){
		$scenario=1;
	}
	if($elemid=='add' || $scenario=='2') {
		//Добавление элементов - делаем малой кровью - предварительно создаём строку в таблице
		d()->db->exec("insert into `".et(url(3))."`  () values ()");
		$elemid=d()->db->lastInsertId();
	}
	if($scenario=='1') {
		//Добавление элементов - делаем малой кровью - предварительно создаём строку в таблице
		$result = d()->db->query("select * from `".et(url(3))."` where `url` = ".e(url(4))."");
		if($result){
			$line=$result->fetch();
			$elemid=$line['id'];
		}else{
			d()->db->exec("insert into `".et(url(3))."`  () values ()");
			$elemid=d()->db->lastInsertId();
		}
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
    $result_str="update `".et(url(3))."` set  ";
    $i=0;
	
	
	foreach($params as $key=>$value) {
		$i++;
		if (substr($key,-3)=='_id' && $value == '') {
			$result_str.=" `" . $key . "`= NULL ";
		} else {
			$result_str.=" `" . $key . "`= ".e($value)." ";
		}
        if ($i<count($params)) $result_str.=' , ';
    }
		
    $result_str.=" where `id`=".(int)($elemid);

 
	$not_reqursy=0;
	
	doitClass::$instance->db->exec($result_str);
	$error_code=doitClass::$instance->db->errorInfo();
	$error_code=$error_code[1];

	if (1054 == $error_code) {
		
		
		$_res=doitClass::$instance->db->query('SHOW COLUMNS FROM `'.et(url(3)).'`');		
		$list_of_existing_columns=array();
		foreach ($_res->fetchAll(PDO::FETCH_NUM) as $_tmpline) {
			$list_of_existing_columns[] = $_tmpline[0];
		}

		foreach($params as  $value=>$key){
			if(!in_array($value,$list_of_existing_columns)){
				doitClass::$instance->Scaffold->create_field(et(url(3)),$value);
			}
		}
		doitClass::$instance->db->exec($result_str);
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
	d()->db->exec("delete from `".et(url(3))."`  where id= ".e(url(4))." ");
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
	$rows = d()->admin['fields'];
	foreach ($rows as $key=>$value) {
		$data[]=array('name'=>$value[1],'type'=>$value[0],'title'=>$value[2],'all'=>$value);
	}
    return $data;
}


function admin_scaffold_new()
{
	if(d()->validate('admin_scaffold_create')){
		$table=d()->params['name'];
		$one_element=to_o(d()->params['name']);
		$_first_letter=strtoupper(substr($one_element,0,1));
		$model = $_first_letter.substr($one_element,1);
		
		
		
		if(d()->params['create_table']=='yes') {
			print "Создаём таблицу ".h($table)."... ";
			$result = d()->db->exec("CREATE TABLE `".$table."` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`url`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`text`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`title`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`".$one_element."_id`  int(11) NULL DEFAULT NULL ,
`template`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`type`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`sort`  int(11) NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
;");
			if($result!==false){
				print "<span style='color:#198E58'>готово</span><br>";
			} else {
				print "<span style='color:#B01414'>неудачно</span><br>";
			}
		}
		
		
		if(d()->params['create_show']=='yes' || d()->params['create_list']=='yes' || d()->params['create_model']=='yes' ) {
			print "Создаём папку mod_".h($table)."... ";
			$result=mkdir($_SERVER['DOCUMENT_ROOT'].'/app/mod_'.$table);
 
			if($result){
				print "<span style='color:#198E58'>готово</span><br>";
			} else {
				print "<span style='color:#B01414'>неудачно</span><br>";
			}
		}
		
		if((d()->params['create_show']=='yes' || d()->params['create_list']=='yes')) {
			include('cms/mod_admin/scaffold_templates/scaffold_templates.php');
			$show_controller_func=str_replace(array('#table#','#one_element#','#model#'),array($table,$one_element,$model),$show_controller_func);
			$list_controller_func=str_replace(array('#table#','#one_element#','#model#'),array($table,$one_element,$model),$list_controller_func);
			$show_template=str_replace(array('#table#','#one_element#','#model#'),array($table,$one_element,$model),$show_template);	
			$list_template=str_replace(array('#table#','#one_element#','#model#'),array($table,$one_element,$model),$list_template);
			$field_template=str_replace(array('#table#','#one_element#','#model#'),array($table,$one_element,$model),$field_template);
			$router_template=str_replace(array('#table#','#one_element#','#model#'),array($table,$one_element,$model),$router_template);
		}
		
		if((d()->params['create_show']=='yes' || d()->params['create_list']=='yes') && !file_exists($_SERVER['DOCUMENT_ROOT'].'/app/mod_'.$table.'/'.$table.'.func.php')) {

			
			
			print "Создаём файл mod_".h($table)."/".h(et($table)).".func.php... ";
			$result=fopen($_SERVER['DOCUMENT_ROOT'].'/app/mod_'.$table.'/'.$table.'.func.php','w+');
			$t_result = fwrite($result,"<"."?php\r\n\r\n");
			fclose($result);
			if($result!=='false' && $t_result!=='false'){
				print "<span style='color:#198E58'>готово</span><br>";
			} else {
				print "<span style='color:#B01414'>неудачно</span><br>";
			}
		}
		
		
		if((d()->params['create_show']=='yes') && file_exists($_SERVER['DOCUMENT_ROOT'].'/app/mod_'.$table.'/'.$table.'.func.php')) {
			
			$check=file_get_contents($_SERVER['DOCUMENT_ROOT'].'/app/mod_'.$table.'/'.$table.'.func.php');
			if(false===strpos($check,$table."_show")){
				print "Создаём функцию  ".h($table)."_show... ";
				$result=fopen($_SERVER['DOCUMENT_ROOT'].'/app/mod_'.$table.'/'.$table.'.func.php','a');
				$t_result = fwrite($result,$show_controller_func);
				fclose($result);
				if($result!=='false' && $t_result!=='false'){
					print "<span style='color:#198E58'>готово</span><br>";
				} else {
					print "<span style='color:#B01414'>неудачно</span><br>";
				}
			}
			
			
		}
		
		
		if((d()->params['create_show']=='yes') && !file_exists($_SERVER['DOCUMENT_ROOT'].'/app/mod_'.$table.'/_show.html')) {
			print "Создаём файл mod_".h($table)."/_show.html... ";
			$result=fopen($_SERVER['DOCUMENT_ROOT'].'/app/mod_'.$table.'/_show.html','w');
			$t_result = fwrite($result,$show_template);
			fclose($result);
			if($result!=='false' && $t_result!=='false'){
				print "<span style='color:#198E58'>готово</span><br>";
			} else {
				print "<span style='color:#B01414'>неудачно</span><br>";
			}
		}
		
		if((d()->params['create_list']=='yes') && file_exists($_SERVER['DOCUMENT_ROOT'].'/app/mod_'.$table.'/'.$table.'.func.php')) {
			
			$check=file_get_contents($_SERVER['DOCUMENT_ROOT'].'/app/mod_'.$table.'/'.$table.'.func.php');
			if(false===strpos($check,$table."_index")){
				print "Создаём функцию  ".h($table)."_index... ";
				$result=fopen($_SERVER['DOCUMENT_ROOT'].'/app/mod_'.$table.'/'.$table.'.func.php','a');
				$t_result = fwrite($result,$list_controller_func);
				fclose($result);
				if($result!=='false' && $t_result!=='false'){
					print "<span style='color:#198E58'>готово</span><br>";
				} else {
					print "<span style='color:#B01414'>неудачно</span><br>";
				}
			}
		}		
		
		if((d()->params['create_list']=='yes') && !file_exists($_SERVER['DOCUMENT_ROOT'].'/app/mod_'.$table.'/_index.html')) {
			print "Создаём файл mod_".h($table)."/_index.html... ";
			$result=fopen($_SERVER['DOCUMENT_ROOT'].'/app/mod_'.$table.'/_index.html','w');
			$t_result = fwrite($result,$list_template);
			fclose($result);
			if($result!=='false' && $t_result!=='false'){
				print "<span style='color:#198E58'>готово</span><br>";
			} else {
				print "<span style='color:#B01414'>неудачно</span><br>";
			}
		}
		
		
		if((d()->params['create_fields']=='yes') && !file_exists($_SERVER['DOCUMENT_ROOT'].'/app/fields/'.$table.'.ini')) {
			print 'Создаём файл fields/'.$table.'.ini... ';
			$result=fopen($_SERVER['DOCUMENT_ROOT'].'/app/fields/'.$table.'.ini','w');
			$t_result = fwrite($result,$field_template);
			fclose($result);
			if($result!=='false' && $t_result!=='false'){
				print "<span style='color:#198E58'>готово</span><br>";
			} else {
				print "<span style='color:#B01414'>неудачно</span><br>";
			}
		}
		
		if((d()->params['create_router']=='yes') && file_exists($_SERVER['DOCUMENT_ROOT'].'/app/router.init.ini')) {
			$check=file_get_contents($_SERVER['DOCUMENT_ROOT'].'/app/router.init.ini');
			if(false===strpos($check,$table."_index") && false===strpos($check,$table."_show")){
				print 'Записываем адреса в роутер... ';
				$result=fopen($_SERVER['DOCUMENT_ROOT'].'/app/router.init.ini','a');
				$t_result = fwrite($result,$router_template);
				fclose($result);
				if($result!=='false' && $t_result!=='false'){
					print "<span style='color:#198E58'>готово</span><br>";
				} else {
					print "<span style='color:#B01414'>неудачно</span><br>";
				}
			}
		}
		
	}
	print d()->view();
}
//Открытие шаблона либо вывод формы авторизации
function admin()
{

	//TODO: переписать на валидаторах
	if(isset($_POST['action']) && $_POST['action']=='admin_login'){
		if(!is_array(d()->admin['editor']['login']) && !is_array(d()->admin['editor']['password'] )){
			d()->datapool['admin']['editor']['login']=array(d()->admin['editor']['login'],array());
			d()->datapool['admin']['editor']['password']=array(d()->admin['editor']['password'],array());
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
