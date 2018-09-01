<?php

/*
DoIt! CMS and VarVar framework
The MIT License (MIT)

Copyright (c) 2011-2016 Damir Fakhrutdinov

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

0.19 Скаффолдинг, ArrayAccess, обработка ошибок, мультиязычность, оптимизация скорости 28.12.2011
0.11 ActiveRecord и foreach для объектов 07.08.2011
0.0 Нулевая версия DoIt CMS
	Рабочее название фреймворка Var(Var) Framework
	Система названа в честь статьи Variable Variables http://php.net/manual/en/language.variables.variable.php 26.01.2011
*/
//error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
error_reporting(0);
session_start();
mb_internal_encoding("UTF-8");

/**
 * Обработчик ошибок, возникающих при работе функций любого типа (шаблоны, функции и т.д.)
 *
 * @param $output Ошибочный вывод.
 * @return string Информация об шибке
 */
function doit_ob_error_handler($output)
{
	$error = error_get_last();
	
	if($error['type']==1){
		$parent_function =  d()->_active_function();




		if(d()->db->errorCode()!=0){
			$db_err=d()->db->errorInfo();
			$_message='<br>Также зафиксирована ошибка базы данных:<br>'. $db_err[2]." (".$db_err[1].")";
			if(iam('developer')){ 
				if($db_err[1] == '1146'){
					$_message.='<br> Создать таблицу <b>'.h(d()->bad_table).'</b>? <form method="get" action="/admin/scaffold/new" style="display:inline;" target="_blank"><input type="submit" value="Создать"><input type="hidden" name="table" value="'.h(d()->bad_table).'"></form> ';
					
					
				}
				if($db_err[1] == '1054'){
					//Попытка создать столбик для таблицы
					//Unknown column 'user_id'
					$_column_name = array();
					if( preg_match_all("/Unknown\scolumn\s\'(.*?)\'/",$db_err[2], $_column_name)==1){
						$_column_name = 	$_column_name[1][0];
					
						$_message.='<br> Создать столбец <b>'.h($_column_name).'</b> в таблице '.h(d()->bad_table).'? <form method="post" action="/admin/scaffold/create_column" style="display:inline;" target="_blank"><input type="submit" value="Создать"><input type="hidden" name="table" value="'.h(d()->bad_table).'"><input type="hidden" name="column" value="'.h($_column_name).'"></form> ';
					}
					
				}
				$_message.='<br> Провести обработку схемы? <form method="get" action="/admin/scaffold/update_scheme" style="display:inline;" target="_blank"><input type="submit" value="Провести"></form><br>';
			}
			
		}
		$errfile = substr($error['file'],strlen($_SERVER['DOCUMENT_ROOT'])) ;
		return print_error_message(' ',$error['line'],$errfile ,$error['message'],'Ошибка при выполнении функции '.$parent_function.' '.$_message );
	}
	return $output;
}

/**
 * Обработчик исключений тип PARSE_ERROR, возникших при загрузке проекта, которые невозможно словить другим способом.
 */
function doit_parse_error_exception()
{
	if($error = error_get_last()){
	
		if($error['type']==4 || $error['type']==64 || $error['type']==4096){
			$errfile = substr($error['file'],strlen($_SERVER['DOCUMENT_ROOT'])) ;
			$lines=file($_SERVER['DOCUMENT_ROOT'].'/'.$error['file']);
			$wrongline=$lines[$error['line']];
			print print_error_message($wrongline,$error['line'],$errfile ,$error['message'],'Ошибка при разборе кода');
		}
	}
}

register_shutdown_function('doit_parse_error_exception');

/**
 * Внутренняя служебная функция для вывода сообщений об ошибке.
 *
 * @param $wrongline Текст строки с ошибкой
 * @param $line Номер строки с ошибкой
 * @param $file Файл
 * @param $message Сообщение системное
 * @param $usermessage Соощение пользователя
 * @param bool $last Скрывать все следающие ошибки после этой.
 * @return string Сформированное сообщение.
 */
function print_error_message($wrongline,$line,$file,$message,$usermessage,$last=false)
{
	static $not_show_me_in_future=false;
	if($not_show_me_in_future){
		return '';
	}
	if($last==true){
		$not_show_me_in_future=true;
	}
	$errfile = substr($file,strlen($_SERVER['DOCUMENT_ROOT'])) ;
	$file_and_line='';
	if($file!='' || $line!=''){
		$file_and_line='<div>Файл '.$file.', строка '.$line.'</div>';
	}
	return '<div style="padding:20px;border:1px solid red;background:white;color:black;">
					<div>'.$usermessage.': '.$message.'</div>'.
					$file_and_line.
					htmlspecialchars($wrongline).'</div>';
}

/**
 * Функция, возвращающая фрагменты текущего URL, начиная с 1.
 * Например, для адреса /users/ainu/comments/3 url(1)="users", url(4)="3", url("users")="ainu", url(2,2)="ainu/comments".
 * Без параметров возвращает весь URL.
 * URL используется преобразованный (например, /users/ -> /users/index)
 *
 * @param $param Номер фрагмента или имя предыдущего фрагмента для поиска. Например, url("comments")=3
 * @param int $length Количество возвращамых фрагментов, включая текущий по умолчанию 1. Если отрицательно, отсчёт идёт с конца
 * @return string Искомый фрагмент
 */
function url($param='', $length=1)
{	
	return d()->url($param,$length);
}

/**
 * Возвращает экземпляр основного объекта системы. Если его не существует, создаёт его.
 * Является обёрткой для паттерна Singletone
 * Если указан необязательный параметр, возвращет свойство основного объекта с указанными именем
 * Например: d('title') или d('User')
 * Более длинная запись функции d()
 *
 * @param string $object (необязательно) Свойство основного объекта
 * @return doitClass Экземпляр основного объекта системы
 */
function doit()
{
	return doitClass::$instance;
	/* DEPRECATED
	return doitClass::$instance->$object;
	*/
}

/**
 * Возвращает экземпляр основного объекта системы. Если его не существует, создаёт его.
 * Является обёрткой для паттерна Singletone
 * Если указан необязательный параметр, возвращет свойство основного объекта с указанными именем
 * Например: d('title') или d('User')
 * Более короткая запись функции doit()
 *
 * @param string $object (необязательно) Свойство основного объекта
 * @return doitClass Экземпляр основного объекта системы
 */
function d()
{
	return doitClass::$instance;
	/* DEPRECATED
	return doitClass::$instance->$object;
	*/
}
/**
 * Объект-прокси.
 * Запускает d()->action(), передавая все полученные параметры. Используется для более короткой записи.
 * Функция d()->action() принимает имя валидатора (имя формы), и в случае её корректности (пришёл $_POST, правила пройдены),
 * запускает одноимёную функцию/метод класса посредством d()->call().
 * Используется для указания того, что именно в этом месте должно происходить действие, и какая функция для этого
 * действия нужна. Вызывается в контроллере до вывода представления.
 *
 * @return mixed Результат работы d()->action, как правило, HTML-код.
 */
function action()
{
	$paramaters = func_get_args();
	return call_user_func_array(array(d(),'action'),  $paramaters);
}


/**
* Класс - заглушка для глушения ошибок PDO. Создаётся, если база данных в проекте не нужна.
* Выводит ошибки только в момент попытки получения данных
*
*/

class PDODummy
{
	function __call($a,$s)
	{
		return $this;
	}
	function __toString()
	{
		print print_error_message('Укажите верные настройки базы данных в файле config.php','','' ,d()->db_error->getMessage(),'Ошибка при подключении к базе данных ' );
		exit();
	}
	function __get($name)
	{
		print print_error_message('Укажите верные настройки базы данных в файле config.php','','' ,d()->db_error->getMessage(),'Ошибка при подключении к базе данных ' );
		exit();
	}
}

/**
 * Основной объект системы
 */
class doitClass
{
	public $is_using_route_all=false;
	public $callables=array();
	public $datapool=array(); //Большой массив всех опций, данных и переменных, для быстрого прямого доступа доступен публично
	public static $instance;
	private $_run_before = false;
	public $fragmentslist=array(); //Массив кода фрагментов и шаблонов.
	public $php_files_list=array(); //Массив найденных php файлов.
	private $ini_database=array(); //Названия существующих ini-файлов, а также факт их использования
	private $for_include=array(); //Массив файлов для последующего инклуда
	private $for_ini=array(); //Массив файлов для последующей загрузки
	private $url_parts=array(); //Фрагменты url, разделённые знаком '/'
	private $url_string=''; //Сформированная строка URL без GET параметров
	private $url_string_raw=''; //Сформированная строка URL без GET параметров и index
	private $call_chain=array(); //Цепь вызовов
	private $call_chain_start=array(); //Текущая функция, корень цепочки
	private $call_chain_current_link=array(); //Текущий элемент цепочки
	private $call_chain_level=0; //текущий уровень, стек для комманд
	private $compiled_fragments=array(); //Кеш шаблонов
	private $template_patterns=array(); //Теги шаблонизатора
	private $template_replacements=array(); //Значения тегов шаблонизатора
	private $_last_router_rule=''; //Активное правило, которое сработало для текущей функции
    public  $lang='ru'; //Текущий язык мультиязычного сайта
	public $_this_cache=array();
	public $db = NULL;
	public $db_error=false;
	private $is_root_func=false;
	private $must_be_stopped=false; //Устанавливается в true при необходимости прервать текущее выполнение
	private $_prepared_content=array();
	private $validate_disabled=false;
	public $langlink='';
	protected $_closures = array();
	public static $autoload_folders = array();
	public $current_route = false; //Последний сработавший роут

	public $_current_include_directory = ''; //Путь, в которых лежат функции-кложуры
	//Автопоиск путей для Кложур
	public $_closure_current_view_path = false; //Пути, в которых искать вьюшки. Сюда пишутся пути, вызываемые фунциями
	public $_closure_directories = array(); //Пути, в которых лежат функции-кложуры
	//Автопоиск путей для роутов
	public $_router_current_view_path = false; //Пути, в которых искать вьюшки. Сюда пишутся пути, вызываемые роутами
	public $_router_directories = array(); //Пути, в которых лежат роуты
	//group
	public $_current_route_basename=false;
	
	public $http_request = false;
	public $http_response = false;
	public $middleware_pipe = false;
	public $events_pool = array();
/* ================================================================================= */	
	function __construct()
	{
		self::$instance = $this;
		
		define ('ROOT',substr( dirname(__FILE__) ,0,-4));
		
		
		//тут описана работа с базой данных
		
		if(!defined('DB_TYPE')){
			define('DB_TYPE','mysql');
		}
		try {
			if(DB_TYPE == 'mysql') {
				define ('DB_FIELD_DEL','`');
				$this->db = new PDO(DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
				$this->db->exec('SET CHARACTER SET utf8');
				$this->db->exec('SET NAMES utf8');
			} else {
				define ('DB_FIELD_DEL','');
				$this->db = new PDO(DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
			}
			
		} catch (PDOException $e) {
			$this->db_error=$e;
			//Создание заголовки для подавления ошибок и доступа к скаффолдингу
			$this->db=new PDODummy();
		}

		// Массив для шаблонизатора
		$this->template_patterns[]='#([^a-zA-Z01-9\)\]\"\\\'\-\+\/])\~([a-zA-Z])#';
		$this->template_replacements[]='$1d()->$2';
		
		// <foreach users as user>
		$this->template_patterns[]=	'/<foreach\s+(.*?)\s+as\s+([a-zA-Z0-9_]+)>/';
		$this->template_replacements[]='<'.'?php $tmparr= $doit->$1;
		if(!isset($doit->datapool[\'this\'])){
			$doit->datapool[\'this\']=array();
		}
		array_push($doit->_this_cache,$doit->datapool[\'this\']);
if(is_string($tmparr)) $tmparr=array($tmparr);
foreach($tmparr as $key=>$subval)
	if(is_string($subval)) print $subval;else {
		$doit->key = $key;
		$doit->datapool["override"]="";
		if(is_object($subval)){
			 $doit->datapool[\'$2\']=$subval;
			 $doit->datapool[\'this\']=$subval;
			 $doit->datapool[\'override\']=$subval->override;
		}else{
		$doit->datapool[\'this\']=array();
		foreach($subval as $subkey=>$subvalue) {
		$doit->datapool[\'$2\'][$subkey]=$subvalue;
		$doit->datapool[\'this\'][$subkey]=$subvalue;
		}   }
		if ($doit->datapool["override"]!="") { print $doit->{$doit->datapool["override"]}(); } else { ?'.'>';


		//TODO: приписать if (is_object($tmparr)) $Tmparr=array($tmparr)
		// TODO: 		foreach($subval as $subkey=>$subvalue) $doit->datapool[$subkey]=$subvalue;
		//	возможно, убрать эту конструкцию

		// <foreach users>
		$this->template_patterns[]='/<foreach\s+(.*)>/';
		$this->template_replacements[]='<'.'?php $tmparr= $doit->$1;

		if(!isset($doit->datapool[\'this\'])){
			$doit->datapool[\'this\']=array();
		}
		array_push($doit->_this_cache,$doit->datapool[\'this\']);
if(is_string($tmparr)) $tmparr=array($tmparr);
foreach($tmparr as $key=>$subval)
	if(is_string($subval)) print $subval;else {
		$doit->key = $key;
		$doit->datapool["override"]="";
		if(is_object($subval)){
			 $doit->datapool[\'this\']=$subval;
			 $doit->datapool[\'override\']=$subval->override;
		}else{
		$doit->datapool[\'this\']=array();
		foreach($subval as $subkey=>$subvalue) {
		$doit->datapool[\'this\'][$subkey]=$subvalue;
		}   }
		if ($doit->datapool["override"]!="") { print $doit->{$doit->datapool["override"]}(); } else { ?'.'>';

		// {* comment *}
		$this->template_patterns[]='#{\*.*?\*}#muis';
		$this->template_replacements[]='';

		// @ print 2+2;
		$this->template_patterns[]='#^\s*@((?!import|page|namespace|charset|media|font-face|keyframes|-webkit|-moz-|-ms-|-o-|region|supports|document).+)$#mui';
		$this->template_replacements[]='<?php $1; ?>';

		
		$this->template_patterns[]='/<tree\s+(.*)>/';
		$this->template_replacements[]='<?php 
		$passed_tree_elements = array();
		$child_branch_name = "$1";
		$call_stack = array();
		$last_next = true;
		d()->level = 0;
		while (true) {
			if(is_object(d()->this)){
				$is_valid = d()->this->valid();
			}else{
				break;
			}
			if($is_valid){
				if(isset($passed_tree_elements[d()->this["id"]])){
					break;
				}
				$passed_tree_elements[d()->this["id"]]=true;
			?>';

				
		$this->template_patterns[]='/<\/tree>/' ;
		$this->template_replacements[]='<?php 
											
			 }
			
			if( isset( d()->this[$child_branch_name]) && count(d()->this[$child_branch_name])>0){
				$call_stack[] = d()->this;
				d()->this = d()->this[$child_branch_name];
				d()->level++;
				continue;
			}else{
				if(is_object(d()->this)){
					if(!d()->this->valid()){
						if( count($call_stack)>0){
							d()->this = array_pop($call_stack);
							d()->level--;
							d()->this->next();
							continue;
						}else {
							break;
						}
					}else{
						d()->this->next();
					}
					continue 1;
				}else{
 					break;
				}
			}
		} ?>';
				
    	
		
		
		// {{{content}}}
		$this->template_patterns[]='/\{{{([#a-zA-Z0-9_]+)\}}}/';
		$this->template_replacements[]='<'.'?php print $doit->render("$1"); ?'.'>';

		// <type admin> //DEPRECATED
//		$this->template_patterns[]='/<type\s+([a-zA-Z0-9_-]+)>/';
//		$this->template_replacements[]='<'.'?php if($doit->type=="$1"){ ?'.'>';

		// <content for header>
		$this->template_patterns[]='/<content\s+for\s+([a-zA-Z0-9_-]+)>/';
		$this->template_replacements[]='<'.'?php ob_start(); $doit->datapool["current_ob_content_for"] = "$1"; ?'.'>';

		// </content>
		$this->template_patterns[]='/<\/content>/';
		$this->template_replacements[]='<'.'?php  $doit->datapool[$doit->datapool["current_ob_content_for"]] = ob_get_contents(); ob_end_clean(); ?'.'>';

		// </foreach>
		$this->template_patterns[]='/<\/foreach>/' ;
		$this->template_replacements[]='<'.'?php } }
		$doit->datapool[\'this\'] = array_pop($doit->_this_cache );
		 ?'.'>';

		// </type>
		$this->template_patterns[]='/<\/type>/';
		$this->template_replacements[]='<'.'?php } ?'.'>';

		/*
		// {{helper 'parame' 'param' 'param2'=>'any'}}
		$this->template_patterns[]='/\{{([#a-zA-Z0-9_]+)\s+([a-Z0-9\s\"\\\']+)\}}/';
		$this->template_replacements[]='{{test}}>';
		*/

		$this->template_patterns[]='#\{{([\\\\a-zA-Z0-9_/]+\.html)}}#';
		$this->template_replacements[]='<'.'?php print $doit->view->partial("$1"); ?'.'>';

		// {{content}}
		$this->template_patterns[]='/\{{([#a-zA-Z0-9_]+)\}}/';
		$this->template_replacements[]='<'.'?php print $doit->call("$1"); ?'.'>';

		// {{helper param}}
		$this->template_patterns[]='/\{{([#a-zA-Z0-9_]+)\s+([a-zA-Z0-9_]+)\}}/';
		$this->template_replacements[]= '<'.'?php print $doit->call("$1", array(d()->$2));  ?'.'>';

		// {{helper 'parame','param2'=>'any'}}
	##	$this->template_patterns[]='/\{{([#a-zA-Z0-9_]+)\s+(.*?)\}}/';
	##	$this->template_replacements[]='<'.'?php print $doit->call("$1",array(array($2))); ?'.'>';

		// <@helper 'parame' param2 = 'any'>
		$this->template_patterns[]='/<@([#a-zA-Z0-9_]+)\s+(.*?)>/';
		$this->template_replacements[]='<'.'?php print $doit->call("$1",array(d()->prepare_smart_array(\'$2\'))); ?'.'>';

		// {{@helper 'parame' param2 = 'any'}}
		$this->template_patterns[]='/\{{\@([#a-zA-Z0-9_]+)\s+(.*?)\}}/';
		$this->template_replacements[]='<'.'?php print $doit->call("$1",array(d()->prepare_smart_array(\'$2\'))); ?'.'>';



        // {if url()==23?}
        $this->template_patterns[]='/\{if\s(.*)\?\}/';
        $this->template_replacements[]='<'.'?php if ($doit->$1) { ?'.'>';

        // {or url()==23?}
        $this->template_patterns[]='/\{(or|elseif)\s(.*)\?\}/';
        $this->template_replacements[]='<'.'?php } elseif($doit->$1) { ?'.'>';


        // {url()==23?}
        $this->template_patterns[]='/\{(.*)\?\}/';
        $this->template_replacements[]='<'.'?php if ($doit->$1) { ?'.'>';


        // {else}
        $this->template_patterns[]='/\{else\}/';
        $this->template_replacements[]='<'.'?php } else { ?'.'>';


        // {(endif)} или {/}
        $this->template_patterns[]='/\{(endif|\/)\}/';
        $this->template_replacements[]='<'.'?php }  ?'.'>';

        // {title}
	##	$this->template_patterns[]='/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/';
	##	$this->template_replacements[]='<'.'?php print  $doit->$1; ?'.'>';

		// {:title}
		$this->template_patterns[]='/\{:([a-zA-Z0-9\._]+)\}/';
		$this->template_replacements[]='<'.'?php } ?'.'>';

		// {title:}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+):\}/';
		$this->template_replacements[]='<'.'?php if($doit->$1) { ?'.'>';

		// <if user>    //DEPRECATED
//		$this->template_patterns[]='/\<if\s([a-zA-Z0-9_]+)\>/';
//		$this->template_replacements[]='<'.'?php if($doit->$1) { ?'.'>';

		// <if user.title>    //DEPRECATED
//		$this->template_patterns[]='/\<if\s([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\>/';
//		$this->template_replacements[]='<'.'?php if((is_array($doit->$1) && $doit->$1[\'$2\']) || $doit->$1->$2) { ?'.'>';

		// {page.title}
	##	$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\}/';
	##	$this->template_replacements[]='<'.'?php if(is_array($doit->$1)) {  print  $doit->$1[\'$2\']; }else{ print  $doit->$1->$2; } ?'.'>';

		//DEPRECATED
		// {page.title:}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+):\}/';
		$this->template_replacements[]='<'.'?php if((is_array($doit->$1) && $doit->$1[\'$2\']) || $doit->$1->$2) { ?'.'>';

		// {.title}
		$this->template_patterns[]='/\{\.([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php if(is_array($doit->this)) {  print  $doit->this[\'$1\']; }else{ print  $doit->this->$1; } ?'.'>';

		// {.title|h}
		$this->template_patterns[]='/\{\.([a-zA-Z0-9_]+)\|([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php if(is_array($doit->this)) {  print  $doit->$2($doit->this[\'$1\']); }else{ print  $doit->$2($doit->this->$1); } ?'.'>';
		
		// </if> //DEPRECATED
//		$this->template_patterns[]='/\<\/if\>/';
//		$this->template_replacements[]='<'.'?php } ?'.'>';

		// {title|h}
	##	$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\|([a-zA-Z0-9_]+)\}/';
	##	$this->template_replacements[]='<'.'?php print  $doit->$2($doit->$1); ?'.'>';


		// {{.image|preview 'parame','param2'=>'any'}}
	##	$this->template_patterns[]='/\{\.([a-zA-Z0-9_]+)\|([#a-zA-Z0-9_]+)\s+(.*?)\}/';
	##	$this->template_replacements[]='<'.'?php print $doit->call("$2",array(array($doit->this[\'$1\'], $3))); ?'.'>';

		// {{image|preview 'parame','param2'=>'any'}}
	##	$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\|([#a-zA-Z0-9_]+)\s+(.*?)\}/';
	##	$this->template_replacements[]='<'.'?php print $doit->call("$2",array(array($doit->$1, $3))); ?'.'>';

		// {{news.image|preview 'parame','param2'=>'any'}}
	##	$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\|([#a-zA-Z0-9_]+)\s+(.*?)\}/';
	##	$this->template_replacements[]='<'.'?php print $doit->call("$3",array(array($doit->$1[\'$2\'], $4))); ?'.'>';


		// {"userlist"|t}
		$this->template_patterns[]='/\{\"(.+?)\"\|([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php print  $doit->$2("$1"); ?'.'>';

		// {page.title|h}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\|([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php if(is_array($doit->$1)) {  print  $doit->$3($doit->$1[\'$2\']); }else{ print  $doit->$3($doit->$1->$2); } ?'.'>';

		// {page.user.title}
	##	$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+).([a-zA-Z0-9_]+)\}/';
	##	$this->template_replacements[]='<'.'?php print  $doit->$1->$2->$3; ?'.'>';

		// {page.user.title|h}
	##	$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+).([a-zA-Z0-9_]+)\|([a-zA-Z0-9_]+)\}/';
	##	$this->template_replacements[]='<'.'?php print $doit->$4( $doit->$1->$2->$3); ?'.'>';

		// {page.parent.user.title}
	##	$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+).([a-zA-Z0-9_]+).([a-zA-Z0-9_]+)\}/';
	##	$this->template_replacements[]='<'.'?php print  $doit->$1->$2->$3->$4; ?'.'>';

		// {page.parent.user.avatar.url}
	##	$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+).([a-zA-Z0-9_]+).([a-zA-Z0-9_]+).([a-zA-Z0-9_]+)\}/';
	##	$this->template_replacements[]='<'.'?php print  $doit->$1->$2->$3->$4->$5; ?'.'>';



		// {{/form}}
		$this->template_patterns[]='/\{{\/([a-zA-Z0-9_]+)\}}/';
		$this->template_replacements[]='</$1>';//Синтаксический сахар

		
		// {=url(0)}
		$this->template_patterns[]='/\{=(.+)\}/';
		$this->template_replacements[]='<'.'?php print  $1; ?'.'>';
		
		//Обрезка GET-параметров
		$_tmpurl=urldecode($_SERVER['REQUEST_URI']);
		
        //Проверка на мультиязычность сайта
        if(substr($_tmpurl,3,1)=='/'){
            $probablyLang=substr($_tmpurl,1,2);
			//Язык /ml/ при отсуствующем файле запрещён
            if(file_exists('app/lang/'.$probablyLang.'.ini')){
                $this->load_and_parse_ini_file('app/lang/'.$probablyLang.'.ini');
                $this->lang=$probablyLang;
                $_tmpurl=substr($_tmpurl,3);
            } else{
				if(file_exists('app/lang/'.$this->lang.'.ini')){
					$this->load_and_parse_ini_file('app/lang/'.$this->lang.'.ini');
				}
			}
        }else{
			if(file_exists('app/lang/'.$this->lang.'.ini')){
				$this->load_and_parse_ini_file('app/lang/'.$this->lang.'.ini');
			}
		}
		$this->langlink='';
		if($this->lang != '' && $this->lang!='ru'){
			$this->langlink='/'.$this->lang;
		}

		$_where_question_sign = strpos($_tmpurl,'?');
		if($_where_question_sign !== false) {
			$_tmpurl = substr($_tmpurl, 0, $_where_question_sign); 
		}
		$this->url_string_raw = $_tmpurl;
		//приписывание в конце слешей index
		if(substr($_tmpurl,-1)=='/') {
			$_tmpurl=$_tmpurl."index";
		}
		
		$this->url_string = $_tmpurl;
		
		//сохранение фрагментов url
		$this->url_parts=explode('/',substr($_tmpurl,1));
		
		$_files=array();
		//сначала инициализируются файлы из ./cms, затем из ./app
		if(isset($_ENV['APP_DIRS']) && $_ENV['APP_DIRS']!=''){
			$_work_folders = array_map('trim', explode(',', $_ENV['APP_DIRS']));
		}else{
			$_work_folders = array('cms','app');
		}
		$ignore_subfolders = array('.','..','internal','external','fields','vendor');
		
		define('SERVER_NAME',preg_replace('/^www./i','',$_SERVER['SERVER_NAME']));
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/sites/'.SERVER_NAME)){
			$_work_folders[]='sites/'.SERVER_NAME;
		}else{
			preg_match('#(^.*?)\.#',SERVER_NAME,$m);
			$subdomain = ($m[1]);
			if(file_exists($_SERVER['DOCUMENT_ROOT'].'/sites/'.$subdomain)){
				$_work_folders[]='sites/'.$subdomain;
			}
		}
		$disabled_modules=array();
		if(defined('DISABLED_MODULES')){
			$disabled_modules=explode(',',DISABLED_MODULES);
		}
		
		$simple_folders = array();
		
		foreach($_work_folders as $dirname) {
			
			$current_ignore_subfolders = $ignore_subfolders;
			
			
			$_files[$dirname]['/']=array();
			$_handle = opendir($_SERVER['DOCUMENT_ROOT'].'/'.$dirname);
			if (!$_handle) {
				continue;
			}
			while (false !== ($_file = readdir($_handle))) {
				if($dirname =='cms'){
					if(isset($_ENV['DOIT_ADMIN_VERSION']) && $_ENV['DOIT_ADMIN_VERSION']=='2' && $_file == 'mod_admin'){
						continue;
					}
					if((!isset($_ENV['DOIT_ADMIN_VERSION']) || $_ENV['DOIT_ADMIN_VERSION']!='2') && $_file == 'admin'){
						continue;
					}
					
				}
				if(substr($_file,0,4)=='mod_') {
					if(!in_array(substr($_file,4), $disabled_modules)){
						$_subhandle = opendir($_SERVER['DOCUMENT_ROOT'].'/'.$dirname.'/'.$_file);
						$_files[$dirname]['/'.$_file.'/']=array();
						while (false !== ($_subfile = readdir($_subhandle))) {
							$_files[$dirname]['/'.$_file.'/'][]=$_subfile;
						}
						closedir($_subhandle);
					}
				} elseif (is_dir($_SERVER['DOCUMENT_ROOT'].'/'.$dirname .'/'. $_file) && !in_array($_file, $ignore_subfolders) ){
					//Модули 2.0, список директорий
					$simple_folders[] = $dirname.'/'.$_file;
					doitClass::_fill_simple_folders_subdirectories($dirname.'/'.$_file, $simple_folders);
				} else {
					$_files[$dirname]['/'][]=$_file;
				}
			}
			closedir($_handle);
		}
		
		$for_include=array();
		$for_ini=array();
		$ini_files_dirs=array();
		$ini_files_local=array();
		
		foreach($_work_folders as $dirname) {

			foreach($_files[$dirname] as $_dir => $_subfiles) {
				foreach($_subfiles as $_file) {

					if ( strrchr($_file, '.')=='.html') {
						$_fragmentname = str_replace('.','_',substr($_file,0,-5));
					} else {
						$_fragmentname = str_replace('.','_',substr($_file,0,-4));
					}
					if (substr($_fragmentname,0,1)=='_') {
						$_fragmentname=substr($_dir,5,-1).$_fragmentname;
					}
					if (strrchr($_file, '.')=='.html') {
						if (substr($_file,-9)!='.tpl.html') {
							$_fragmentname .= '_tpl';
						}	
						$this->fragmentslist[$_fragmentname] = $dirname.$_dir.$_file;
						continue;
					}
					
					//Контроллер - функции для работы с данными и бизнес-логика. Работа шаблонизатора подавлена.
					if (substr($_file,-9)=='.func.php') {
						$this->for_include[$_dir.$_file]=$dirname.$_dir.$_file;
						
						continue;
					}
					if (strrchr($_file, '.')=='.php') {
						$this->php_files_list[$_fragmentname] = $dirname.$_dir.$_file;
						continue;
					}
					
					//Обработка факта наличия .ini-файлов
					if (strrchr($_file, '.')=='.ini') {
						//Правила, срабатывающие в любом случае, инициализация опций системы  и плагинов
						if (substr($_file,-8)=='init.ini') {
							//Если имя файла оканчивается на .init.ini, инициализировать его сразу
							$this->for_ini[$_dir.$_file]=($dirname.$_dir.$_file);
						} else {
							//При первом запросе адрес сбрасывается в false для предотвращения последующего чтения
							//Хранит адрес ini-файла, запускаемого перед определённой функцией //DEPRECATED

							$_dir_file=($_dir.$_file);

							
							//Реалзация приоритетов: одноимённый файл из папки app переопределит тотже из папки cms
							if(isset($ini_files_dirs[$_dir_file])){
								foreach($this->ini_database as $_key=> $_ininame){
									foreach($_ininame as $key=>$value){
										if($value==$ini_files_dirs[$_dir_file]){
											unset($this->ini_database[$_key][$key]);
										}
									}
								}
							}
							$ini_files_dirs[$_dir_file]=$dirname.$_dir.$_file;
							if(isset($this->ini_database[substr($_file,0,-4)])){
								$this->ini_database[substr($_file,0,-4)][]=$dirname.$_dir.$_file;
							}else{
								$this->ini_database[substr($_file,0,-4)]=array($dirname.$_dir.$_file);
							}
						}
						continue;
					}
				}
			}
			
			
			
		}
		$autoload_folders = array();
		
		
		foreach($simple_folders as $folder){
			
			
			$_handle = opendir($_SERVER['DOCUMENT_ROOT'].'/'.$folder);

			while (false !== ($_file = readdir($_handle))) {
				//ищем php файлы
				
				if (strrchr($_file, '.')=='.php' || is_dir($_SERVER['DOCUMENT_ROOT'].'/'.$folder.'/'.$_file)) {
					$fistrsim = $_file{0};
					if($fistrsim>='A' && $fistrsim<='Z'){
						//это класс
						$autoload_folders[$folder]=true;
					}else{
						$this->for_include[$folder.'/'.$_file] = $folder.'/'.$_file;
					}
				}
				
			}
			//создаём план работы над директориями и их кодом
			//PHP файлы инклудим
			//HTML файлы запоминаем
		}
		
		foreach($this->for_ini as $value) {
			$this->load_and_parse_ini_file ($value);
		}
		
		doitClass::$autoload_folders = array_keys($autoload_folders);
		spl_autoload_register(function  ($class_name) {

			$class_name = ltrim($class_name, '\\');
			$fileName  = '';
			$namespace = '';
			if ($lastNsPos = strripos($class_name, '\\')) {
				$namespace = substr($class_name, 0, $lastNsPos);
				$class_name = substr($class_name, $lastNsPos + 1);
				$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
			}
			$fileName_simple = $fileName .  $class_name . '.php';
			$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
			//$fileName = 'vendors'.DIRECTORY_SEPARATOR.$fileName;
			$lover_class_name=strtolower($class_name);
			
			foreach (doitClass::$autoload_folders as $path){
				 
				
				if(is_file($_SERVER['DOCUMENT_ROOT'].'/'. $path . '/'.$fileName  )){
					require $_SERVER['DOCUMENT_ROOT'].'/'. $path . '/'.$fileName ;
					return;
				}
				if(is_file($_SERVER['DOCUMENT_ROOT'].'/'. $path . '/'.$fileName_simple  )){
					require $_SERVER['DOCUMENT_ROOT'].'/'. $path . '/'.$fileName_simple ;
					return;
				}	
				
			}
			 

		},true,true);
		
		if(PHP_VERSION_ID > 50408) {
			$this->http_request = Zend\Diactoros\ServerRequestFactory::fromGlobals(
				$_SERVER,
				$_GET,
				$_POST,
				$_COOKIE,
				$_FILES
			);
			
			$this->http_response = new Zend\Diactoros\Response();
			
			
			$this->middleware_pipe=new Zend\Stratigility\MiddlewarePipe();
			
			
		}
		
		foreach($this->for_include as $value) {
			
			$this->_current_include_directory = dirname($_SERVER['DOCUMENT_ROOT'].'/'.$value);
			
			$this->_current_route_basename = false;
			
			if(is_file($_SERVER['DOCUMENT_ROOT'].'/'.$value)){
				include($_SERVER['DOCUMENT_ROOT'].'/'.$value);	
			}
			$this->_current_route_basename = false;
		}
		
		//Отрабатывает роутинг
		if($this->is_using_route_all){

			$url = $this->url_string;
			$uparts = array();
			preg_match_all('#\/([0-9a-zA-Z_]+)\/.*#',$url,$uparts);
			$upart_found=false;
			if(isset($uparts[1][0]) && (class_exists($uparts[1][0].'controller'))){
				//Мы находимся по адресу /users/ и у нас есть контроллер users. Строго гоовря, мы готовы.
				$sub_uparts = array();
				foreach (doitClass::$instance->datapool['urls'] as $rule){
					preg_match_all('#\^?\/([0-9a-zA-Z_]+)\/.*#',$rule[0],$sub_uparts);
					if(isset($sub_uparts[1][0]) && $sub_uparts == $uparts[1][0]){
						$upart_found=true;
						break;
					}					
				}
				if(!$upart_found){
					route($uparts[1][0]);
				}
			}
		}
		
		
		
		d()->bootstrap();
		
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/app/static') && strpos($this->url_string,'..')===false && $this->url_string !='/'    && file_exists($_SERVER['DOCUMENT_ROOT'].'/app/static'.$this->url_string) && is_file($_SERVER['DOCUMENT_ROOT'].'/app/static'.$this->url_string) ){
			
			$this->compiled_fragments['doit_open_static_file'] = $this->shablonize(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/app/static'.$this->url_string));
			$this->_prepared_content['main'] = $this->compile_and_run_template('doit_open_static_file');
		}
		
		if(isset($_POST['_global']) && $_POST['_global']=='1'){
			if(isset($_POST['_run_before']) && $_POST['_run_before']!=''){
				$this->_run_before = $_POST['_run_before'];
			}else{
				$this->validate_global_handlers();
			}
		}
		
		 
	}

	
	
	/* VERSION 2.0 */
	public $routes=array();
	function route($adress, $closure=false){
		$route = new Route();
		$route->map($adress, $closure);
		$route->initiateAutoFind($this->_current_include_directory);
		$this->routes[]=$route;
		return $route;
	}
	
	function post($adress, $closure=false){
		$route = new Route();
		$route->map($adress, $closure);
		$route->method = array('POST');
		$route->initiateAutoFind($this->_current_include_directory);
		$this->routes[]=$route;
		return $route;
	}
	
	function get($adress, $closure=false){
		$route = new Route();
		$route->map($adress, $closure);
		$route->method = array('GET');
		$route->initiateAutoFind($this->_current_include_directory);
		$this->routes[]=$route;
		return $route;
	}
	
	function group($url, $closure=false){
		$this->_current_route_basename = $url;
		if($closure!==false){
			$closure();
		}
	}
	
	function dispatch($level='content'){
 
		
		$accepted_routes = array();
		$url=urldecode(strtok($_SERVER["REQUEST_URI"],'?'));
		foreach($this->routes as $route){
			if($route->check($url,$_SERVER['REQUEST_METHOD'])){
				$accepted_routes[]=$route;
			}
		}
		if(count($accepted_routes)){
			$this->current_route = $accepted_routes[0];
			$result = $accepted_routes[0]->dispatch($url);
			$this->current_route = false;
			return $result;
		}
		return false;
	}
	function new_pipe()
	{
		return new Zend\Stratigility\MiddlewarePipe();
	}
	function add($path=false, $middleware = null){
		/* обёртка для запуска как иконки {{add}}, так и для добавления middleware */
		if(is_array($path) || $path === false){
			return d()->call('add',array($path));
		}
		$this->middleware_pipe->pipe($path, $middleware);
	}
	
	function pipe($path, $middleware = null){
		$this->middleware_pipe->pipe($path, $middleware);
	}
	function write($text){
		$this->http_response->getBody()->write($text);
	}
	/* Функция, стартующая вообще всё. */
	function main(){
		
		if(PHP_VERSION_ID > 50408) {
			$this->middleware_pipe->pipe(function($request, $response, $next){
				$response->getBody()->write($this->call('main'));
			});
			
			$this->middleware_pipe->pipe(function ($err, $request, $response, $next) {
				if(iam('developer')){
					$trace = $err->getTrace();
					$result .= '<br>Trace:<br>';
					foreach($trace as $val){
						$result .=  $val['file'] .':'. $val['line']. '<br />';
					}
					$result .= '<br />';
					print print_error_message('Выброшено исключение',$err->getLine(),$err->getFile() ,$err->getMessage()."<br>".$result,'Исключение' );
				}else{
					print print_error_message('Выброшено исключение',' ',' ' ,$err->getMessage(),'Исключение' );
				}
				exit;
			});
			
			$pipe = $this->middleware_pipe;
			$this->http_response = $pipe($this->http_request, $this->http_response);
			foreach ($this->http_response->getHeaders() as $name => $values) {
				foreach ($values as $value) {
					header(sprintf('%s: %s', $name, $value), false);
				}
			}
			return $this->http_response->getBody();
		}
		return $this->call('main');
	}
	
	/*
		Функция, загружающая контент страницы
	*/
	public function content()
	{
		//1. (пропускаем) ищем функции (роуты, которые мы можем выполнить, и выполняем их)
		//3. Передаём дальше в content
		//d()->router->dispatch();
		$result = d()->dispatch();
		if($result === false){
			return d()->call('content');
		}
		return $result;
		
		
	}
	/* END VERSION 2.0 */
	
	

	
	/**
	 * Проверяет данные, полученные с формы, учитывая опции валидатора и пользовательские функции. Также проверяет факт
	 * получения $_POST как такового, например, если обязательных данных нет. В случае ошибки возвращает false.
	 *
	 * @param $validator_name Имя валидатора (указывается в форме и ini-файлах валидатора).
	 * @param $params Массив параметров, пришедших с формы
	 * @param array $additional_funcs Массив дополнительных пользовательских функций для проверки
	 * @return bool true, если валидация пройдена
	 */
	public function validate_action($validator_name,$params,$additional_funcs=array())
	{
		unset($additional_funcs[0]);
		$is_ok=true;
		
		if (isset($this->validator[$validator_name])) {
			$rules=$this->validator[$validator_name];
	//		if(!isset($this->datapool['notice'])) {
				if(isset($this->datapool['notice']) && is_array($this->datapool['notice']) && count($this->datapool['notice'])>0){
					//некоторые правила были добавлены в валидатор. Остальные сработать не должны
					return false;
				}else{
					$this->datapool['notice']=array();	
				}
				$this->datapool['inputs_with_errors']=array();
	//		}

			foreach($rules as $key=>$value) {
				if($key=='function') {
					continue;
				}
				if(isset($value['required']) && (!isset ($params[$key]) || trim($params[$key])=='')) {
					$this->datapool['notice'][] = $value['required']['message'];
					$this->datapool['inputs_with_errors'][] = $key;
					$is_ok=false;
				}
				if(isset($value['confirmation']) && (!isset ($params[$key.'_confirmation']) || $params[$key.'_confirmation']!=$params[$key])) {
					$this->datapool['notice'][] = $value['confirmation']['message'];
					$this->datapool['inputs_with_errors'][] = $key.'_confirmation';
					$is_ok=false;
				}
				if(isset($value['unique'])) {
					if(isset($value['unique']['table'])) {
						$table=$value['unique']['table'];
						$model=ucfirst(d()->to_o($table));
					}
					if(isset($value['unique']['model'])) {
						$model =ucfirst($value['unique']['model']);
						$table = d()->to_p(strtolower($value['unique']['model']));
					}
					if (! d()->$model->find_by($key,$params[$key])->is_empty){
						$this->datapool['notice'][] = $value['unique']['message'];
						$this->datapool['inputs_with_errors'][] = $key;
						$is_ok=false;
					}
				}
				if(isset($value['function'])) {
					if(!is_array($value['function'])) {
						$value['function']=array($value['function']);
					}
					foreach($value['function'] as $func) {
						$rez = $this->call($func,array($params[$key]));
						if($rez===false){
							if (count($this->datapool['notice'])!=0){
								$is_ok=false;
							}
						}
					}
				}
				foreach($value as $rule => $rule_array){
					 if( !in_array($rule,array('unique','required','function','confirmation'))){
						 $rez = $this->call($rule,array($params[$key],$rule_array));
						 if($rez===false){
							 $this->datapool['notice'][] = $value[$rule]['message'];
							 $this->datapool['inputs_with_errors'][] = $key;
							 $is_ok=false;
						 }
					}
				}
			}

			//дополнительные функции с правилами для валидаторов
			if(isset($rules['function'])) {
				if(!is_array($rules['function'])) {
					$rules['function']=array($rules['function']);
				}
				foreach($rules['function'] as $func) {
					$rez = $this->call($func,array($params));
					if($rez===false){
						if (count($this->datapool['notice'])!=0){
							$is_ok=false;
							return $is_ok;
						}
					}
				}
			}
		}
		foreach($additional_funcs as $func) {
			$rez = $this->call($func,array($params));
			if($rez===false){
				if (count($this->datapool['notice'])!=0){
					$is_ok=false;
					return $is_ok;
				}	
			}
		}
		foreach($additional_funcs as $func) {
			$this->call($func,array($params));
		}
		if (count($this->datapool['notice'])!=0){
			$is_ok=false;
		}
		
		return $is_ok;
	}

		
	private function validate_global_handlers()
	{
		if(isset($_POST['_global_sign']) && isset($_SESSION['_form_sign_key']) && $_SESSION['_form_sign_key']!=''){
			$current_sign = $_POST['_global_sign'];
			$run_before_sign='';
			if(isset($_POST['_run_before']) && $_POST['_run_before']!=''){
				$run_before_sign = md5($_POST['_run_before']);
			}
			
			$real_sign = sha1('salt_sign'.md5($_SESSION['_form_sign_key']).md5($_POST['_element']).md5($_POST['_action']).$run_before_sign);
			if($real_sign===$current_sign){
				//подпись верна, считаем данные верными
				return d()->call($_POST['_action']);
			}
		}
		return '';
		
	}

	/**
	 * Добавляет сообщение об ошибке валидации формы в существующий список
	 *
	 * @param $text Текст ошибки
	 */
	public function add_notice($text,$element=false)
	{
		$this->datapool['notice'][] = $text;
		if($element!==false){
			$this->datapool['inputs_with_errors'][] = $element;
		}
		
	}	

	/**
	 * Функция принимает имя валидатора (имя формы), и в случае её корректности (пришёл $_POST, правила пройдены),
	 * запускает одноимёную функцию/метод класса посредством d()->call().
	 * Используется для указания того, что именно в этом месте должно происходить действие, и какая функция для этого
	 * действия нужна. Вызывается в контроллере до вывода представления.
	 * Может принимать дополнительные параметры - пользовательские функции-валидаторы
	 * Рекомендуется использовать другой подход, при помощи функции d()->validate()
	 * Записывает параметры формы в массив d()->params для дальнейшего использования.
	 *
	 * @param $action_name Имя функции/валидатора/формы, например send_mail или users#create
	 * @return mixed|string|void Возвращает результат отработавшей функции (как правило, HTML-код)
	 */
	public function action($action_name)
	{
		//Обработка actions. Ничего не выводится.
		//параметры, тобы передавать в action(дополнительные функции для проверки)
		$parameters = func_get_args();

		if(isset($_POST['_is_simple_names']) && $_POST['_is_simple_names']=='1'){
			$values =  $_POST;
			if(isset($_POST[$_POST['_element']]) && is_array($_POST[$_POST['_element']]) && count($_POST[$_POST['_element']])>0){
				foreach($_POST[$_POST['_element']] as $key=>$value){
					$values[$key]=$value;
				}
			}
		}else{
			$values =  $_POST[$_POST['_element']];
		}
		if(isset($_POST) && isset($_POST['_action']) && ($action_name == $_POST['_action']) && ($this->validate_action($_POST['_action'], $values,$parameters ))) {
			$this->datapool['params'] =  $values;
			return $this->call($_POST['_action'],array($_POST[$_POST['_element']]));
		}
	}

	/**
	 * Проверяет корректность правила валидации/названия формы, а также факт POST-запроса, и если все правила верны,
	 * возвращает true и заполняет массив d()->params параметрами формы
	 * В отличие от d()->action, не требует существования одноимённой функции.
	 * Использование:
	 * if(d()->validate('send_mail')) {
	 *     mail(d()->params['user'],'Письмо','Письмо');
	 * }
	 *
	 * @param $action_name Имя валидатора/формы
	 * @return bool Корректность заполненной информации
	 */
	public function validate($action_name=false)
	{
		if($this->validate_disabled){
			return false;
		}
		if($action_name===false & isset($_POST['_action']) && strpos($_POST['_action'],'#')!==false && isset($_POST['_global']) && '1' == $_POST['_global']){
			$action_name=$_POST['_action'];
		}
		$parameters = func_get_args();

				if(isset($_POST['_is_simple_names']) && $_POST['_is_simple_names']=='1'){
			$values =  $_POST;
			if(isset($_POST[$_POST['_element']]) && is_array($_POST[$_POST['_element']]) && count($_POST[$_POST['_element']])>0){
				foreach($_POST[$_POST['_element']] as $key=>$value){
					$values[$key]=$value;
				}
			}
		}else{
			$values =  $_POST[$_POST['_element']];
		}
		
		if(isset($_POST) && isset($_POST['_action']) && ($action_name == $_POST['_action']) && ($this->validate_action($_POST['_action'], $values,$parameters ))) {
			$this->datapool['params'] = $values;
			return true;
		}
		return false;
	}

	/**
	 * Функция, возвращающая фрагменты текущего URL, начиная с 1.
	 * Например, для адреса /users/ainu/comments/3 url(1)="users", url(4)="3", url("users")="ainu", url(2,2)="ainu/comments"
	 * Без параметров возвращает весь URL.
	 * URL используется преобразованный (например, /users/ -> /users/index)
	 *
	 * @param $param Номер фрагмента или имя предыдущего фрагмента для поиска. Например, url("comments")=3
	 * @param int $length Количество возвращамых фрагментов, включая текущий по умолчанию 1. Если отрицательно, отсчёт идёт с конца
	 * @return bool|string Искомый фрагмент
	 */
	public function url($param='',$length=1)
	{
		if($param=='') {
			$param = 1;
			$length = count($this->url_parts);
		}
		if($length<=0) {
			$length = count($this->url_parts) + $length - 1;
		}
		if(!is_numeric($param)) {
			
			//url('users')
			$readyindex=false;
			$i=0;
			foreach ($this->url_parts as $key => $value) {
				$i++;
				if($value==$param) {
					$readyindex = $i;
					break;
				}
			}
			if ($readyindex === false) {
				return false;
			}
			$param = $readyindex + 1; 
		}
		//TODO: возвращать false	
		$tmpstr = '';
		for($i=0;$i<=$length-1;$i++) {
			if ($i > 0) {
				$tmpstr.= '/';
			}
			$tmpstr.= $this->url_parts[$param + $i - 1];
		}
		return $tmpstr;
	}

	/**
	 * Вызывает методы основного класса (функции), используя всевозможные переопределения, проверки и так далее.
	 * d()->call('func') это полный аналог d()->func()
	 * В случае наличия решётки, создаёт экземпляр (если не создан) и вызывает метод класса контроллера
	 * d('users#create') == d()->users_controller->create();
	 * В первую очередь ищет функцию с подходящим именем, затем .php файл с подходящим именем, затем .html c подходящим
	 * именем. Если существует и функция и html файл с одинаковыми именами, для доступа к html-файлу используется
	 * суффикс _tpl (например, d()->call('users_show_tpl')). При наличии правила в роутере вызывает несколько функций
	 * согласно правилам.
	 * Если запущен в виде d()->call('func1','func2','func3'), то запускает функции по-очереди в не зависимости от
	 * правил роутера
	 * [DEPRECATED] При наличии одноимённого ini файла (т.е. без суффикса .init.ini) проанализирает и загрузит его.
	 *
	 * @param $name Имя функции/класса-метода/php-файла/шаблона
	 * @param array $arguments Массив параметров, передаваемых в вызываемую функцию
	 * @return mixed|string|void Результат (как правило, HTML-код)
	 */
	public function call($name, $arguments=array())
	{
		
		$fistrsim = $name{0};
		if($fistrsim>='A' && $fistrsim<='Z'){
			return new $name($arguments);
		}
		
		if(!$this->is_root_func){
			$this->is_root_func = true;
			$i_am_root=true; //Если установлено, данная функция - первая
		}else{
			$i_am_root=false;
			if($this->must_be_stopped){
				return '';
			}
		}
		
		if(isset($this->_prepared_content[$name])){
			$content=$this->_prepared_content[$name];
			unset($this->_prepared_content[$name]);
			return $content;
		}
		
		//DEPRECATED, отмена использования конструкции
		/*
		if (count($arguments)!=0 && is_array($arguments[0])) {
			foreach($arguments[0] as $key=>$value) {
				$this->datapool[$key]=$value;
			}
		}
		*/
		
		
		if($this->_run_before !== false && $this->_run_before==$name){
			$this->_run_before = false;
			print $this->validate_global_handlers();
		}	
		
		$_result_end='';
		if (!is_array($arguments)) {
			$_newnames = func_get_args();  //d()->call('first','second','clients#edit','clients_tpl');
			$arguments=array();
		} else {
			$_newnames = $this->get_function_alias($name);
		}
		$_currentname=$name;
		$_continuechain = true;
		
		if($this->_active_function()==$name){
			$this->validate_disabled=true;
		}
		for($i=0;$i<=count($_newnames)-1;$i++) {
			$_newname = $_newnames[$i];
			//DEPRECATED - сделать явные вызовы

			$name=$_newname;
			//Проверка на существование фрагмента fragment_tpl, если самой функции нет
			if ( (!function_exists($name)) && (!isset($this->php_files_list[$name])) && (isset( $this->fragmentslist[$name."_tpl"]))) {
				$name = $name."_tpl";
			}
			$this->call_chain_level++; //поднимаем уровень текущего стека очереди
			//Сохраняем текущую цепочку команд
			$this->call_chain[$this->call_chain_level] = $_newnames;
			$this->call_chain_start[$this->call_chain_level]=$_currentname;
			$this->call_chain_current_link[$this->call_chain_level]=$i;
			//Тут вызываются предопределённые и пользовательские функции
			ob_start('doit_ob_error_handler');
			$been_controller=false;
			if(isset($this->datapool[$name]) && ($this->datapool[$name] instanceof Closure)) {
				//Сохраняем путь, в котором был инициирована Closure
				$this->_closure_current_view_path = $this->_closure_directories[$name];
				$_executionResult=call_user_func_array($this->datapool[$name], $arguments);
			}elseif (function_exists($name)) {

				//Подстановка аргументов из $this->_last_router_rule
				//$this->_last_router_rule содержит активное правило роутера (например, "/users/")
				//Передача параметров URL в методы классов и функции
				if(count($arguments)==0 && $this->_last_router_rule!='') {
					$params_arr=explode('/',substr($this->_last_router_rule,1));
					foreach($this->url_parts as $_part_num => $_part_val){
						if(!isset($params_arr[$_part_num]) || $params_arr[$_part_num]==''){
							$arguments[]=$_part_val;
						}
					}
				}
				
				$_executionResult=call_user_func_array($name, $arguments);
				$been_controller=true;
			} elseif(isset($this->php_files_list[$name])){
				include ($_SERVER['DOCUMENT_ROOT'].'/'.$this->php_files_list[$name]);
				$been_controller=true;
			}elseif(isset($this->callables[$name])){
				$_executionResult=call_user_func_array($this->callables[$name], $arguments);
			} else {
				$_fsym=strpos($name,'#');
				if($_fsym !== false) {

					//Подстановка аргументов из $this->_last_router_rule
					//дублирование кода, расположенного выше (для скорости)
					//Для того, чтобы не проводить операцию для шаблонов и включаемых php-файлов
					if(count($arguments)==0 && $this->_last_router_rule!='') {
						$params_arr=explode('/',substr($this->_last_router_rule,1));
						foreach($this->url_parts as $_part_num => $_part_val){
							if(!isset($params_arr[$_part_num]) || $params_arr[$_part_num]==''){
								$arguments[]=$_part_val;
							}
						}
					}

					$_classname=substr($name,0,$_fsym).'Controller';
					
					$_first_letter=strtoupper(substr($_classname,0,1));
					$_classname = $_first_letter.substr($_classname,1);

					$_methodname=substr($name,$_fsym+1);
					$_chain_method=$_methodname;
					if($_methodname=='') {
						if(is_numeric($arguments[0])){
							$_methodname = 'show';
							$_chain_method=$_methodname;
						}else{
							if($arguments[0]==''){
								$_methodname='index';
								$_chain_method=$_methodname;
							}else{		
								if(method_exists($_classname,$arguments[0])){
									$_methodname=$arguments[0];
									$_chain_method=$_methodname;
								}else{
									$_methodname='show';
									//Если файл controller_$arguments.html существует то все нормально
									if(substr($name,0,$_fsym) == 'pages' && isset($this->fragmentslist[substr($name,0,$_fsym).'_'.$arguments[0].'_tpl'])){
										$_chain_method=$arguments[0];
									}else{
										$_chain_method='show';
									}
								}
							}
							unset($arguments[0]);
						}
						//В случае вызова controller# переменовывается цепочка для нормального определения вида исход из имени метода
						$this->call_chain[$this->call_chain_level][$this->call_chain_current_link[$this->call_chain_level]]=$name.$_chain_method;
					}
					//$_executionResult=call_user_func_array(array($this->universal_controller_factory($_classname), $_methodname), $arguments);
					call_user_func_array(array($this->{$_classname}, 'before'), array($_methodname, $arguments));
					$_executionResult=call_user_func_array(array($this->{$_classname}, $_methodname), $arguments);
					call_user_func_array(array($this->{$_classname}, 'after'), array($_methodname, $arguments));
					$been_controller=true;
				
				} else {
					$_executionResult= $this->compile_and_run_template($name);
				}
			}
			$_end = ob_get_contents();
			ob_end_clean();
			
			
			
			if($been_controller && ($_end=='') && (is_null($_executionResult) || $_executionResult=='')){
				
				//Определяем функцию (контроллер), из которого был произведён вызов. Припиываем _tpl, вызываем
				$parent_function =  $this->_active_function();
				if(substr($parent_function,-4)!='_tpl'){
					$parent_function .= '_tpl';
					$parent_function =  str_replace('#','_',$parent_function);
					if(isset($this->fragmentslist[$parent_function])){
						ob_start('doit_ob_error_handler');
						$_executionResult= $this->call($parent_function);
						$_end = ob_get_contents();
						ob_end_clean();
					}
				}
			}
			
			if (!is_null($_executionResult)) {
				$_end = $_executionResult;
			}
			//Загружаем актуальную цепочку команд. call_chain могла измениться
			$_newnames = $this->call_chain[$this->call_chain_level];
			$i = $this->call_chain_current_link[$this->call_chain_level];
			$this->call_chain_level--; //опускаем уровень текущего стека очереди
			if (count($_newnames)==1) {
				if($i_am_root && $this->must_be_stopped){
					$this->validate_disabled=false;
					return $this->do_redirect();
				}
				$this->is_root_func=false;
				$this->validate_disabled=false;
				return $_end;
			} else {
				$_result_end .= $_end;
			}
		}
		if($i_am_root && $this->must_be_stopped){
			$this->validate_disabled=false;
			return $this->do_redirect();
		}
		$this->is_root_func=false;
		$this->validate_disabled=false;
		return $_result_end;
	}

	/**
	 * Возвращает скомпилированный в PHP шаблон на основе HTML-файла.
	 * Исползуется ленивая (lazy) загрузка, если файл не был запрошен, он не будет загружен и обработан,
	 * если файл уже запрашивался, отдаются данные из кеша.
	 *
	 * @param $fragmentname Имя фаргмента (шаблона)
	 * @return mixed PHP-код шаблона, готовый к запуску
	 */
	function get_compiled_code($fragmentname)
	{
		if(!isset ($this->compiled_fragments[$fragmentname])) {
			return $this->compiled_fragments[$fragmentname]=$this->shablonize(file_get_contents($this->fragmentslist[$fragmentname]));
		}
		return $this->compiled_fragments[$fragmentname];
	}

	/**
	 * Функция для eval
	 *
	 * Подготавливает новую функцию для предотвращения повторных eval-ов и запускает её.
	 * По сути, имея название шаблона, eval-ит его с экономией процессорного времени.
	 *
	 * @param $name имя шаблона вида file_tpl
	 * @return void значение, полученное из шаблона при помощи return.
	 */
	function compile_and_run_template($name){
		if(!function_exists($name)){
			ob_start(); //Подавление стандартного вывода ошибок Parse Error
			$result=eval('function '.$name.'(){ $doit=d(); ?'.'>'.$this->get_compiled_code($name).'<'.'?php ;} ');
			ob_end_clean();
			if ( $result === false && ( $error = error_get_last() ) ) {
 				$lines = explode("\n",'function '.$name.'(){ $doit=d(); ?'.'>'.$this->get_compiled_code($name).'<'.'?php ;} ');
				$file = $this->fragmentslist[$name];
				return print_error_message( $lines [$error['line']-1],$error['line'],$file,$error['message'],'Ошибка при обработке шаблона',true);
			} else {
				return call_user_func($name);
			}


		}else{
			return call_user_func($name);
		}

	}

	/**
	 * Кaллер (caller), срабатывает при всех возможных запросах вроде d()->func()
	 * Полностью передаёт управление и параметры методу d()->call()
	 *
	 * @param $name Имя функции/php-файла/щаблона
	 * @param $arguments Массив параметров для передачи функции
	 * @return mixed|string|void Результат, как правило, HTML-код
	 */
	public function __call($name, $arguments)
	{
		return 	$this->call($name, $arguments);
	}

	public static function _fill_simple_folders_subdirectories($path, &$simple_folders){
		
		$_handle = opendir($_SERVER['DOCUMENT_ROOT'].'/'.$path);
		if (!$_handle) {
			return;
		}
		while (false !== ($_file = readdir($_handle))) {
			 if ($_file{0}=="+" && is_dir($_SERVER['DOCUMENT_ROOT'].'/'.$path .'/'. $_file)  ) {
				$simple_folders[] = $path.'/'.$_file;
				doitClass::_fill_simple_folders_subdirectories($path.'/'.$_file, $simple_folders); 
			 }
		}
		closedir($_handle);
		
	}

	/**
	 * Фабрика экземпляров контроллеров
	 * universal_controller_factory('clients_controller') вернёт существующий экземпляр класса clients_controller,
	 * или создаст его и вернёт. Аналог d() или doit() для пользовательских классов. Не используется напрямую,
	 * запускается автоматически при попутке запросить
	 * d()->users_controller->method()
	 *
	 * @param $name имя класса контроллера (в виде clients_controller).
	 * @return mixed
	 */
	public function universal_controller_factory($name)
	{
		static $controllers =array(); //Склад контроллеров
		if (! isset ($controllers[$name])) {
			$controllers[$name] = new  $name();
		}
		return $controllers[$name];
	}

	/**
	 * Записывает в реесто переменную для дальнейшего использования
	 *
	 * @param $name Имя переменной
	 * @param $value Значение
	 */
	function __set($name,$value)
	{
		unset($this->_closures[$name]);
		if( is_object($value) && ($value instanceof Closure)){
			$this->_closure_directories[$name] = $this->_current_include_directory;
		}
		$this->datapool[$name]=$value;
	}

	function singleton($name,$closure){
		$this->datapool[$name] = $closure;
		$this->_closure_directories[$name] = $this->_current_include_directory;
		$this->_closures[$name] = true; //является синглтоном
	}
	
	/**
	 * Получает из реестра значение переменной либо, при её отстуствии, запускает допольнитмельные функции, такие как
	 * фабрика классов, фабрика моделей d()->User, и другие, могут быть заданы в ini-файлах
	 *
	 * @param $name Имя переменной
	 * @return mixed Значение
	 */
	function &__get($name)
	{
	;
		//Одиночная загрузка .ini файла при первом обращении к переменной
		if (isset($this->ini_database[$name])) {
			$this->load_and_parse_ini_file($this->ini_database[$name]);
			
			unset ($this->ini_database[$name]);
		}
		
		if(isset($this->datapool[$name])) {
			if( is_object($this->datapool[$name]) && ($this->datapool[$name] instanceof Closure)){
				$closure = $this->datapool[$name];
				if(isset($this->_closures[$name])){ //синглтон
					$result = $closure();
					$this->datapool[$name] = $result;
					return $result;
				}
				//не синглтон, обычный контейнер
				$result = $closure();
				return $result;
			}
			return $this->datapool[$name];
		}
		
		//$fistrsim =  ord(substr($name,0,1));
		//if($fistrsim>64 && $fistrsim<91){
		if(preg_match('/^[A-Z].+/', $name)) {
			$result = new $name();
			return $result;
		}

		//Проверка префиксов для модулей для модулей и расширений
		//TODO: это слишком медленно
		//DEPRECATED
		foreach ($this->datapool['prefixes'] as $_one_prefix) {
			if(preg_match($_one_prefix[0], $name)) {
				return $this->{$_one_prefix[1]}($name);
			}
		}
		
		if($name!='this'){
			if(is_object($this->this)) {
				return $this->this->{$name};	
			}
			if(is_array($this->this)) {
				return  $this->this[$name];
			}			
		}
		return '';
	}


	public function __isset($name) {
		if (isset($this->ini_database[$name])) {
   			$this->load_and_parse_ini_file($this->ini_database[$name]);
     			unset($this->ini_database[$name]);
  		}
		return isset($this->datapool[$name]);
	}
	 
	public function __unset($name) {
		unset($this->datapool[$name]);
	}
 
	/**
	 * Возвращает имя текущей функции (триады). Даже если в её теле запускались другие функции, текущая не потеряется
	 * Внутри вложенных функций текущая функция будет другой. Внутренняя. Используется при обработке ошибок
	 * и при запуске d()->view()
	 * @return mixed Название функции
	 */
	public function _active_function()
	{
		return $this->call_chain[$this->call_chain_level][$this->call_chain_current_link[$this->call_chain_level]];
	}

	/**
	 * Запускает имя_функции.tpl.html, либо пытается угадать имя текущей триады
	 * Будучи запущенной из функции d()->users_list, запускает d()->users_list_tpl(),
	 * Будучи запущенной из функции d()->users_controller->list, также запускает d()->users_list_tpl(),
	 * Предаёт управление в d()->call(), так что все переопределения разрешены.
	 *
	 * @param string|boolean $parent_function Имя функции
	 * @return mixed|string|void Результат, HTML-код
	 */
	public function view($parent_function=false)
	{
		
		
		//Определяем функцию (контроллер), из которого был произведён вызов. Припиываем _tpl, вызываем
		if($parent_function===false) {
			$parent_function =  $this->_active_function();
		}
		if(substr($parent_function,-4)!='_tpl'){
			$parent_function .= '_tpl';
		}
		$parent_function =  str_replace('#','_',$parent_function);
		return $this->call($parent_function);
	}


	/**
	 * При использовании цепочки функций (когда роутер вместо одной функции переопределяет сразу несколько):
	 * /contacts   content  page_show  feedback_show   maps_show
	 * Меняет следующий элемент цепочки. Если мы использует контролер и шаблон как цепочку, позволяет менять шаблон
	 * Если в цепочке элементов нет (например, функция только одна), то просто выполнит запрошенную
	 * функцию после текущей. При этом переопределения при помощи роутера уже не используются.
	 *
	 * @param $chainname Имя функции/php-файла/контролера-метода/html-шаблона
	 */
	public function set_next_chain($chainname)
	{
		$this->call_chain[$this->call_chain_level][$this->call_chain_current_link[$this->call_chain_level]+1] = $chainname;
	}
/* ================================================================================= */

	/**
	 * При использовании цепочки функций (когда роутер вместо одной функции переопределяет сразу несколько):
	 * /contacts   content  page_show  feedback_show   maps_show
	 * Удаляет все последующие функции из цепочки, следующие за текущей.
	 */
	public function stop_next_chains()
	{
		$this->call_chain_current_link[$this->call_chain_level] = count($this->call_chain[$this->call_chain_level])+1;
	}

	/**
	 * Пока не работает
	 * @param $chainname
	 */
	public function insert_next_chain($chainname)
	{
		//$this->call_chain[$this->call_chain_level][$this->call_chain_current_link[$this->call_chain_level]+1] = $chainname;
	}

	//Устанавливает правила для дальнейшего анализа	цепочки
	//DEPRECATED: слишком сложно
	function route_to($routename='')
	{
		if($routename=='') {
			return;
		}
		$parent_root = $this->call_chain_start[$this->call_chain_level];
		
		$addition_array=array();
		foreach($this->datapool['urls'] as $rule) {
			if (($rule[0]==$routename) && ($rule[1]==$parent_root)) {
				unset($rule[1]);
				unset($rule[0]);
				$addition_array = $rule;
				foreach($addition_array as $element) {
					array_push ($this->call_chain[$this->call_chain_level],$element);
				}
				break;
			}
		}
	}


	//Проверяет URL и анализирует текущий массив правил, при наличии подходящего, возвращает массив всевдонимов (цепочку)
	function get_function_alias($name)
	{
		static $cache_ansver=array(); //Кеш ответов для быстрого реагирования
		static $cache_longest_url_ansver=array(); //Кеш ответов для быстрого реагирования
		static $rules_array = false; //Ассоциативный массив правил для того, чтобы не опрашивать весь список
		if ($name===false){
			$cache_ansver=array(); 
			$cache_longest_url_ansver=array();
			$rules_array = false;
			return false;
		}
		if($rules_array===false) {	
			$tmp_mached_list = array();
			foreach($this->datapool['urls'] as $rule) {
				$rule_place = str_replace('.html', '_tpl', $rule[1]);
				if(!isset($tmp_mached_list[$rule_place])) {
					$tmp_mached_list[$rule_place] = array();
				}
				$tmp_mached_list[$rule_place][] = $rule;
			}
			$rules_array = $tmp_mached_list;
		}

		$this->_last_router_rule='';

		if(!isset($rules_array[$name])) {
			return array($name);
		}
		
		if(isset($cache_ansver[$name])) {
			$this->_last_router_rule=$cache_longest_url_ansver[$name];
			return $cache_ansver[$name];
		}

		$matched=array('','',$name);
		$longest_url='';
		$longest_url_length=0;
		$_requri = $this->url_string;

		//Определение наиболее подходящего правила в списке правил роутинга. Наиболее длинное из подходящих - приоритетнее.
		foreach($rules_array[$name] as $key=>$value) {
			$strlen_value_0 = strlen($value[0]);
			if (is_numeric($key) && ($strlen_value_0 > $longest_url_length) && (
				$_requri==$value[0]
				|| substr($_requri,0,$strlen_value_0)==$value[0] 
				|| preg_match('/^'.str_replace('\/\/','\/.+?\/',str_replace('/','\/',preg_quote($value[0]))).'.*/',$_requri)
				|| ($value[0]{0} === '^' &&  preg_match('#' . $value[0] . '#',$_requri))
				)) {
					$matched=$value;
					$longest_url=$value[0];
					$longest_url_length = strlen($longest_url);
				}
		}
		unset($matched[0]);
		unset($matched[1]);
		
		$matched = str_replace('.html', '_tpl', $matched );
		
		$matched=array_values($matched);
		$cache_ansver[$name] = $matched;
		$cache_longest_url_ansver[$name] = $longest_url;
		$this->_last_router_rule=$cache_longest_url_ansver[$name];
		return $matched;
	}
/* ================================================================================= */
	function shablonize($_str)
	{	
		

		$_str   = preg_replace($this->template_patterns,$this->template_replacements,str_replace(array("\r\n","\r"),array("\n","\n"),$_str));	
		$_str = preg_replace('#{\.(.*?)}#','{this.$1}',$_str);
		$_str = preg_replace_callback( "#\{((?:[a-zA-Z_]+[a-zA-Z0-9_]*?\.)*[a-zA-Z_]+[a-z0-9_]*?)}#mui", function($matches){
			d()->matches = ($matches);
			$string = $matches[1]; //user.comments.title
			$substrings = explode('.',$string);
			
			$result = '<?php print '.doitClass::$instance->compile_advanced_chain($substrings). '; ?>';
			/*
			$first = array_shift($substrings);
			$result = '<?php print $doit->'.$first . implode('',array_map(function($str){
				return "->".$str."";
			},$substrings)) . '; ?>';   //$user ['comments']  ['title']*/
			return $result;
		}, $_str);
			
		
		$_str = preg_replace_callback( "#\{((?:[a-z0-9_]+\.)*[a-z0-9_]+)((?:\|[a-z0-9_]+)+)}#mui", function($matches){
			d()->matches = ($matches);
			$string = $matches[1]; //user.comments.title
 
			$substrings = explode('.',$string);
			
			
			$result = '  '.doitClass::$instance->compile_advanced_chain($substrings);
			/*
			$first = array_shift($substrings);
			
			$result = '  $doit->'.$first . implode('',array_map(function($str){
				return "->".$str."";
			},$substrings)) . ' ';   //$user ['comments']  ['title']
			*/
			
			$functions = $matches[2]; //|h|title|htmlspecialchars
			$substrings = (explode('|',$functions));
			array_shift($substrings);
			$result = '<?php print  ' . array_reduce($substrings, function($all, $item){
				return '$doit->'.$item.'('. $all .')';
			}, $result) .  ' ; ?>'; 
			
			return $result;
		}, $_str);
		
		$_str = preg_replace_callback( "#\{((?:[a-z0-9_]+\.)*[a-z0-9_]+)((?:\|.*?)+)}#mui", function($matches){
			d()->matches = ($matches);
			$string = $matches[1]; //user.comments.title
 
			$substrings = explode('.',$string);
			
			
			$result = '  '.doitClass::$instance->compile_advanced_chain($substrings);
			
			/*
			$first = array_shift($substrings);
			$result = '  $doit->'.$first . implode('',array_map(function($str){
				return "['".$str."']";
			},$substrings)) . ' ';   //$user ['comments']  ['title']
			*/
			$functions = $matches[2]; //|h|title|htmlspecialchars
			$substrings = (explode('|',$functions));
			array_shift($substrings);
			$result = '<?php print  ' . array_reduce($substrings, function($all, $item){
			
				preg_match('#([a-z0-9_]+)(\s+.*)?#',$item,$m);
				if(is_null($m[2])){
					return '$doit->'.$m[1].'('. $all .')';
				}else{
				
					$attr_params = $m[2]; //'50', '100' '200' user="10"   ===>   '50', '100', '200', 'user'=>"10"
					
					$attr_params = preg_replace('#\s+=\s+\\\'(.*?)\\\'#',' => \'$1\' ',$attr_params);
					$attr_params = preg_replace('#\s+=\s+\\"(.*?)\\"#',' => "$1" ',$attr_params);
					$attr_params = preg_replace('#([\s\$a-zA-Z0-9\\"\\\']+)=\s([\s\$a-zA-Z0-9\\"\\\']+)#','$1=>$2',$attr_params);
					$attr_params = preg_replace('#\s+([a-z0-9_]+?)\s*=>#',' \'$1\' => ',$attr_params);
					return '$doit->'.$m[1].'(array('. $all .', '. $attr_params .'))';
				}
				
			}, $result) .  ' ; ?>'; 
			
			return $result;
		}, $_str);
		

		$_str = preg_replace_callback( "/{{([#a-zA-Z0-9_]+)\s+(.*?)\}}/mui", function($matches){
			//file_put_contents('1.txt',json_encode($matches));
			$attr_params = ' '.$matches[2];
			$attr_params = preg_replace('#\s+=\s+\\\'(.*?)\\\'#',' => \'$1\' ',$attr_params);
			$attr_params = preg_replace('#\s+=\s+\\"(.*?)\\"#',' => "$1" ',$attr_params);
			$attr_params = preg_replace('#([\s\$a-zA-Z0-9\\"\\\']+)=\s([\s\$a-zA-Z0-9\\"\\\']+)#','$1=>$2',$attr_params);
			$attr_params = preg_replace('#\s+([a-z0-9_]+?)\s*=>#',' \'$1\' => ',$attr_params);
			return '<?php print $doit->call("' .$matches[1] . '",array(array('.$attr_params.')));?>';
		
		}, $_str);
		
		
		
		return $_str;
		
	}

	
	function compile_advanced_chain($arr){
		
		$str='';
		foreach($arr as $key=>$value){
			if($key==0){
				$str = '$_c_tmp=$doit->'.$value.'';
			}else{
				$str = '$_c_tmp=(is_object('.$str.')?$_c_tmp->'.$value.':$_c_tmp["'.$value.'"])';
			}
			
		}
		return $str;
		
	}
	
	
	
	/**
	 * Выводит значение переменной, либо, при её отсуствии, запускает соотвествующую одноимённую функцию
	 * Таким образом, если запускать d()->render('content') вместо d()->content(), можно заранее в коде переопределить
	 * content нужным нам образом, просто присвоив переменной d()->content нужное значение.
	 * Используется для того, чтобы иметь возможность переопределить основной шаблон (main_tpl) изнутри кода,
	 * выполняемого в content.
	 * Если пемеременная представляет собой массив, выводит все элементы массива подряд.
	 * Практически всегда может заменить и вывод переменной и запуск функции, для большей гибкости.
	 * Запись в шаблоне: {{{content}}}
	 *
	 * @param $str Имя переменной/функции/php-файла/контроллера-метода/html-файла
	 * @return mixed|string|void
	 */
	function render($str)
	{
		if (isset($this->datapool[$str])) {
			if (is_array($this->datapool[$str])) {
				$result='';
				foreach($this->datapool[$str] as $value) {
					$result .= $value;
				}
				return $result;
			} else {
				return $this->datapool[$str];
			}
		} else {
			return  $this->call($str);
		}
	}	

	/**
	 * Загружает ini-файл, распознаёт его, и записывает его содержимое в реестр.
	 * В случае ошибки загрузки возвращает   false.
	 *
	 * @param $filename Имя файла (начиная от корня сайта, включая app/mod*)
	 * @return bool false в случае ошибки
	 */
	function load_and_parse_ini_file($filename){
	 
		if(is_array($filename)){
			foreach($filename as $name){
				$this->load_and_parse_ini_file($name);
			}
		}
		if(!$ini=file($_SERVER['DOCUMENT_ROOT'].'/'.$filename,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) return false;
		$res=array();
		$currentGroup='';
		$arrayKeys=array();
		foreach($ini as $row) {
			$first_symbol=substr(trim($row),0,1);
			if($first_symbol==';') continue; //Комментарии строки игнорируются
			if ($first_symbol=='[') { //Начало новой группы [group]
				$currentGroup=substr($row,1,-1);
				continue;
			}
			$delimeterPos=strpos($row,'=');
			if($delimeterPos===false) {
				//Если тип строки - неименованный массив, разделённый пробелами
				$subject=$currentGroup;

				$tmparr = explode(' ',str_replace("\t",' ',$row));
				$value=array();
				$quoteflag=false;
				$tmpstr="";
				foreach ($tmparr as $val) {
					if ($val!='') {  //игнорирование двойных пробелов между значениями
						if(substr($val,0,1)=='"' && $quoteflag==false) {
							if(substr($val,-1,1)=='"') {
								$value[]=substr($val,1,-1); //Одиночное слово в кавычках
							} else {
								$tmpstr=$val;
								$quoteflag=true;
							}
						} else {
							if(substr($val,-1,1)=='"' && $quoteflag==true) {
								$tmpstr.=' '.$val;
								$value[]=substr($tmpstr,1,-1); //Кавычки закрываются
								$quoteflag=false;
							} else {
								if ($quoteflag==true) {
									$tmpstr.=' '.$val;
								} else {
									$value[]=$val;
								}
							}
						}
					}
				}
				
				if (!isset($arrayKeys[$currentGroup])) {
					$arrayKeys[$currentGroup]=0;
				}
				//Разбор пар ключ-значение
				$founded = false;
				//$value2=$value;
			/*	foreach($value as $number => $element) {
					if(substr($element,-1,1)==':') {
						$value2[substr($element,0,-1)] = $value[$number+1];
						unset($value2[$number]);
						unset($value2[$number+1]);						
					}
				}*/
				
				$value=array($arrayKeys[$currentGroup]=>$value);
				$arrayKeys[$currentGroup]++; //Генерация номера элемента массива, массив нельзя перемешивать с обычными данными
				
			} else {
				$subject= rtrim(substr($row,0,$delimeterPos));
				if ($currentGroup!='') {
					$subject = $currentGroup . '.' . $subject;
				}
				$value=ltrim(substr($row,$delimeterPos+1));
				if(substr($value,0,5) == 'json:'){
					$value = json_decode(substr($value ,5),true);
				}
			}
			if (strpos($subject,'.')===false) {
				$res=array_merge_recursive ($res,array($subject=>$value));
			} else {
				$tmpvalue=$value;
				$tmparr=array_reverse(explode('.',$subject));
				foreach($tmparr as $subSubject) {
					$tmpvalue=array($subSubject=>$tmpvalue);
				}
				$res=array_merge_recursive ($res,$tmpvalue);
			}
		}
		$this->datapool=array_merge_recursive ($this->datapool,$res);
	}
	
	function error($error_page)
	{
		if($error_page == '404'){
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); 
			header("Status: 404 Not Found");
		}
		return d()->redirect('/error_'.$error_page);
	}
	
	function redirect($url)
	{
		//Обрезка GET-параметров
		$_tmpurl=urldecode($url);

		$_where_question_sign = strpos($_tmpurl,'?');
		if($_where_question_sign !== false) {
			$_tmpurl = substr($_tmpurl, 0, $_where_question_sign); 
		}
		
		//приписывание в конце слешей index
		if(substr($_tmpurl,-1)=='/') {
			$_tmpurl=$_tmpurl."index";
		}
		$this->url_string = $_tmpurl;
		
		//сохранение фрагментов url
		$this->url_parts=explode('/',substr($_tmpurl,1));
		$this->get_function_alias(false);
		$this->must_be_stopped=true;
		return true;
	}
	
	function do_redirect()
	{
		$this->is_root_func=false;
		$this->must_be_stopped=false;
		return d()->call('main');
	}
	function prepare_content($function,$content)
	{
		$this->_prepared_content[$function]=$content;
	}
	
	/**
	 * Перенапрвляет браузер при помощ заголовка и завершает код скрипта.
	 * В случае использования AJAX делает немного магии.
	 * 
	 * @param $url Адрес для пернаправления
	 */
	function reload($url=false)
	{
		if(AJAX){
			print $this->Ajax->get_compiled_response();
			header('Content-type: text/javascript; Charset=UTF-8');
			exit();
		}
		if($url==false) {
			$url=$_SERVER['REQUEST_URI'];
		}
		header('Location: '.$url);
		exit();
	}
	
	
	function prepare_smart_array($string)
	{
		$res=array();
		$res_keyvalue=array();
		$p_arr= array();
		preg_match_all('/[\'\"]?([a-zA-Z0-9_]+)[\'\"]?\s*\=\s*[\'\"](.*?)[\'\"]/i',$string,$p_arr);
		foreach($p_arr[1] as $key=>$value){
			$res_keyvalue[$value] = $p_arr[2][$key];
			$string = str_replace($p_arr[0][$key], '',$string);
		}
		$p_arr= array();
		preg_match_all('/[\'\"]([a-zA-Z0-9_]*)[\'\"]/i',$string,$p_arr);
		foreach($p_arr[1] as $key=>$value){
			$res[] = $p_arr[1][$key];
		}
		foreach($res_keyvalue as $key=>$value){
			$res[$key] = $value;
		}
		
		return $res;
	}

	function on($event, $function){
		if(!isset($this->events_pool[$event])){
			$this->events_pool[$event]=array();
		}
		$this->events_pool[$event][] = $function;
	}

	function emit($event, $data=array()){
		if(isset($this->events_pool[$event])){
			$result=true;
			foreach($this->events_pool[$event] as $callback){
				if($result !== false){
					$result = call_user_func_array($callback,array($data,$event));
				}else{
					return false;
				}
			}
		}
	}

	function current_version()
	{
		static $result = null;
		if (!isset($result)) {
			$result = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/cms/VERSION.txt') || 'default';
		}
		return $result;
	}
}
	if(file_exists('vendor/autoload.php')){
		require_once ('vendor/autoload.php');	
	}

	require_once ($_SERVER['DOCUMENT_ROOT'].'/cms/vendor/autoload.php');
/**
 * Автоматический создатель классов и загрузчик классов по спецификации PSR-0
 * Ищет файлы вида class_name.class.php, затем ищет классы в папке vendors по спецификации PSR-0.
 * Если ничего не найдено, создаёт класс и экземпляр класса ar (ActiveRecord)
 *
 * @param $class_name Имя класса
 */
 
 spl_autoload_register(function  ($class_name) {

	$class_name = ltrim($class_name, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strripos($class_name, '\\')) {
        $namespace = substr($class_name, 0, $lastNsPos);
        $class_name = substr($class_name, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
	//$fileName = 'vendors'.DIRECTORY_SEPARATOR.$fileName;
	$lover_class_name=strtolower($class_name);
	if(isset(d()->php_files_list[$lover_class_name.'_class']) && is_file($_SERVER['DOCUMENT_ROOT'].'/'. d()->php_files_list[$lover_class_name.'_class'])){
		require $_SERVER['DOCUMENT_ROOT'].'/'.d()->php_files_list[$lover_class_name.'_class'];
	}elseif(is_file($_SERVER['DOCUMENT_ROOT'].'/'.('vendors'.DIRECTORY_SEPARATOR.$fileName))){
		require $_SERVER['DOCUMENT_ROOT'].'/'.'vendors'.DIRECTORY_SEPARATOR.$fileName;
	}else{
		if(substr(strtolower($class_name),-10)!='controller' && $class_name{0}>='A' && $class_name{0}<='Z'){
			//Если совсем ничего не найдено, попытка использовать ActiveRecord.
			eval ("class ".$class_name." extends ActiveRecord {}");	
		}
	}

});

/**
* Функция, которая ничего не делает.
*/
function nothing()
{
	
}


if (!isset(doitClass::$instance)) {
	new doitClass();
}

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest'){
	define ('AJAX',true);
} else {
	define ('AJAX',false);
}
/* END OF cms.php */
