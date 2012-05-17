<?php
/*
DoIt! CMS and VarVar framework
Copyright (C) 2011 Fakhrutdinov Damir (aka Ainu)

*      This program is free software; you can redistribute it and/or modify
*      it under the terms of the GNU General Public License as published by
*      the Free Software Foundation; either version 2 of the License, or
*      (at your option) any later version.
*      
*      This program is distributed in the hope that it will be useful,
*      but WITHOUT ANY WARRANTY; without even the implied warranty of
*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*      GNU General Public License for more details.
*      
*      You should have received a copy of the GNU General Public License
*      along with this program; if not, write to the Free Software
*      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
*      MA 02110-1301, USA.

0.19 Скаффолдинг, ArrayAccess, обработка ошибок, мультиязычность, оптимизация скорости 28.12.2011
0.11 ActiveRecord и foreach для объектов 07.08.2011
0.0 Нулевая версия DoIt CMS
	Рабочее название фреймворка Var(Var) Framework
	Система названа в честь статьи Variable Variables http://php.net/manual/en/language.variables.variable.php 26.01.2011
*/
//error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
error_reporting(0);
session_start();


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
			$_message='<br>Также зафиксирована ошибка базы данных:<br>'. $db_err[2]." (".$db_err[1].")<br>";
			if(iam('developer')){ 
				if($db_err[1] == '1146'){
					$_message.=' Создать таблицу <b>'.h(d()->bad_table).'</b>? <form method="get" action="/admin/scaffold/new" style="display:inline;" target="_blank"><input type="submit" value="Создать"><input type="hidden" name="table" value="'.h(d()->bad_table).'"></form><br>';
				}
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
			$lines=file($error['file']);
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
	public $datapool=array(); //Большой массив всех опций, данных и переменных, для быстрого прямого доступа доступен публично
	public static $instance;
	
	private $fragmentslist=array(); //Массив кода фрагментов и шаблонов.
	public $php_files_list=array(); //Массив найденных php файлов.
	private $ini_database=array(); //Названия существующих ini-файлов, а также факт их использования
	private $for_include=array(); //Массив файлов для последующего инклуда
	private $for_ini=array(); //Массив файлов для последующей загрузки
	private $url_parts=array(); //Фрагменты url, разделённые знаком '/'
	private $url_string=''; //Сформированная строка URL без GET параметров
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
/* ================================================================================= */	
	function __construct()
	{
		self::$instance = $this;
		
		
		
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
		
		// <foreach users as user>
		$this->template_patterns[]=	'/<foreach\s+(.*?)\s+as\s+([a-zA-Z0-9_]+)>/';
		$this->template_replacements[]='<'.'?php $tmparr= $doit->$1;
		if(!isset($doit->datapool[\'this\'])){
			$doit->datapool[\'this\']=array();
		}
		array_push($doit->_this_cache,$doit->datapool[\'this\']);
if(is_string($tmparr) || (is_array($tmparr) && (count($tmparr)!=0) && !array_key_exists(0,$tmparr))) $tmparr=array($tmparr);
foreach($tmparr as $key=>$subval)
	if(is_string($subval)) print $subval;else {
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
if(is_string($tmparr) || (is_array($tmparr) && (count($tmparr)!=0) && !array_key_exists(0,$tmparr))) $tmparr=array($tmparr);
foreach($tmparr as $key=>$subval)
	if(is_string($subval)) print $subval;else {
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


		// {{content}}
		$this->template_patterns[]='/\{{([#a-zA-Z0-9_]+)\}}/';
		$this->template_replacements[]='<'.'?php print $doit->call("$1"); ?'.'>';

		// {{helper param}}
		$this->template_patterns[]='/\{{([#a-zA-Z0-9_]+)\s+([a-zA-Z0-9_]+)\}}/';
		$this->template_replacements[]= '<'.'?php print $doit->call("$1", array(d()->$2));  ?'.'>';

		// {{helper 'parame','param2'=>'any'}}
		$this->template_patterns[]='/\{{([#a-zA-Z0-9_]+)\s+(.*?)\}}/';
		$this->template_replacements[]='<'.'?php print $doit->call("$1",array(array($2))); ?'.'>';

		
		

		
		// {title}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php print  $doit->$1; ?'.'>';

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
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php if(is_array($doit->$1)) {  print  $doit->$1[\'$2\']; }else{ print  $doit->$1->$2; } ?'.'>';

		//DEPRECATED
		// {page.title:}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+):\}/';
		$this->template_replacements[]='<'.'?php if((is_array($doit->$1) && $doit->$1[\'$2\']) || $doit->$1->$2) { ?'.'>';

		// {.title}
		$this->template_patterns[]='/\{\.([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php if(is_array($doit->this)) {  print  $doit->this[\'$1\']; }else{ print  $doit->this->$1; } ?'.'>';

		// {.title|h}
		$this->template_patterns[]='/\{\.([a-zA-Z0-9_]+)\|([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php if(is_array($doit->this)) {  print  $2($doit->this[\'$1\']); }else{ print  $2($doit->this->$1); } ?'.'>';
		
		// </if> //DEPRECATED
//		$this->template_patterns[]='/\<\/if\>/';
//		$this->template_replacements[]='<'.'?php } ?'.'>';

		// {title|h}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\|([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php print  $doit->$2($doit->$1); ?'.'>';

		// {"userlist"|t}
		$this->template_patterns[]='/\{\"(.+?)\"\|([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php print  $doit->$2("$1"); ?'.'>';

		// {page.title|h}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\|([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php if(is_array($doit->$1)) {  print  $doit->$3($doit->$1[\'$2\']); }else{ print  $doit->$3($doit->$1->$2); } ?'.'>';

		// {page.user.title}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+).([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php print  $doit->$1->$2->$3; ?'.'>';

		// {page.parent.user.title}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+).([a-zA-Z0-9_]+).([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php print  $doit->$1->$2->$3->$4; ?'.'>';

		// {page.parent.user.avatar.url}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+).([a-zA-Z0-9_]+).([a-zA-Z0-9_]+).([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php print  $doit->$1->$2->$3->$4->$5; ?'.'>';



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
		
		$_files=array();
		//сначала инициализируются файлы из ./cms, затем из ./app
		foreach(array('cms','app') as $dirname) { 
			$_files[$dirname]['/']=array();
			$_handle = opendir($dirname);

			while (false !== ($_file = readdir($_handle))) {
				 if(substr($_file,0,4)=='mod_') {
					$_subhandle = opendir($dirname.'/'.$_file);
					$_files[$dirname]['/'.$_file.'/']=array();
					while (false !== ($_subfile = readdir($_subhandle))) {
						$_files[$dirname]['/'.$_file.'/'][]=$_subfile;
					}
					closedir($_subhandle);
				 } else {
					$_files[$dirname]['/'][]=$_file;
				 }
			}
			closedir($_handle);
		}

		$for_include=array();
		$for_ini=array();
		foreach(array('cms','app') as $dirname) {
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
							$this->ini_database[substr($_file,0,-4)]=$dirname.$_dir.$_file;
						}
						continue;
					}
				}
			}
		}
 
		foreach($this->for_include as $value) {
			include($value);
		}

		foreach($this->for_ini as $value) {
			$this->load_and_parse_ini_file ($value);
		}
		
		d()->bootstrap();

	}

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
		
		if (isset($this->datapool['validator'][$validator_name])) {
			$rules=$this->datapool['validator'][$validator_name];
	//		if(!isset($this->datapool['notice'])) {
				$this->datapool['notice']=array();
	//		}

			foreach($rules as $key=>$value) {
				if($key=='function') {
					continue;
				}
				if(isset($value['required']) && (!isset ($params[$key]) || trim($params[$key])=='')) {
					$this->datapool['notice'][] = $value['required']['message'];
					$is_ok=false;
				}
				if(isset($value['confirmation']) && (!isset ($params[$key.'_confirmation']) || $params[$key.'_confirmation']!=$params[$key])) {
					$this->datapool['notice'][] = $value['confirmation']['message'];
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


	/**
	 * Добавляет сообщение об ошибке валидации формы в существующий список
	 *
	 * @param $text Текст ошибки
	 */
	public function add_notice($text)
	{
		$this->datapool['notice'][] = $text;
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
		if(isset($_POST) && isset($_POST['_action']) && ($action_name == $_POST['_action']) && ($this->validate_action($_POST['_action'], $_POST[$_POST['_element']],$parameters ))) {
			$this->datapool['params'] =  $_POST[$_POST['_element']];
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
	public function validate($action_name)
	{
		$parameters = func_get_args();
		if(isset($_POST) && isset($_POST['_action']) && ($action_name == $_POST['_action']) && ($this->validate_action($_POST['_action'], $_POST[$_POST['_element']],$parameters ))) {
			$this->datapool['params'] =  $_POST[$_POST['_element']];
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
		
		//Одиночная загрузка .ini файла при первом обращении к функции
		//Также мы можем вручную привязать ini-файл к любой функции/шаблону
		//DEPRECATED - сделать явные вызовы
		if (isset($this->ini_database[$name])) {
			$this->load_and_parse_ini_file($this->ini_database[$name]);
			unset ($this->ini_database[$name]);
		}
		
		//DEPRECATED, отмена использования конструкции
		/*
		if (count($arguments)!=0 && is_array($arguments[0])) {
			foreach($arguments[0] as $key=>$value) {
				$this->datapool[$key]=$value;
			}
		}
		*/
		$_result_end='';
		if (!is_array($arguments)) {
			$_newnames = func_get_args();  //d()->call('first','second','clients#edit','clients_tpl');
			$arguments=array();
		} else {
			$_newnames = $this->get_function_alias($name);
		}
		$_currentname=$name;
		$_continuechain = true;
		for($i=0;$i<=count($_newnames)-1;$i++) {
			$_newname = $_newnames[$i];
			//DEPRECATED - сделать явные вызовы
			if (isset($this->ini_database[$_newname])) {
				$this->load_and_parse_ini_file($this->ini_database[$_newname]);
				unset ($this->ini_database[$_newname]);
			}
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
			if (function_exists($name)) {

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
				include ($this->php_files_list[$name]);
				$been_controller=true;
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

					if($_methodname=='') {
						if(is_numeric($arguments[0])){
							$_methodname = 'show';
						}else{

							if($arguments[0]==''){
								$_methodname='index';
							}else{
								$_methodname=$arguments[0];
							}
							unset($arguments[0]);
						}
						//В случае вызова controller# переменовывается цепочка для нормального определения вида исход из имени метода
						$this->call_chain[$this->call_chain_level][$this->call_chain_current_link[$this->call_chain_level]]=$name.$_methodname;
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
					return $this->do_redirect();
				}
				$this->is_root_func=false;
				return $_end;
			} else {
				$_result_end .= $_end;
			}
		}
		if($i_am_root && $this->must_be_stopped){
			return $this->do_redirect();
		}
		$this->is_root_func=false;
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
		$this->datapool[$name]=$value;
	}

	/**
	 * Получает из реестра значение переменной либо, при её отстуствии, запускает допольнитмельные функции, такие как
	 * фабрика классов, фабрика моделей d()->User, и другие, могут быть заданы в ini-файлах
	 *
	 * @param $name Имя переменной
	 * @return mixed Значение
	 */
	function __get($name)
	{
		if(isset($this->datapool[$name])) {
			return $this->datapool[$name];
		}

		//$fistrsim =  ord(substr($name,0,1));
		//if($fistrsim>64 && $fistrsim<91){
		if(preg_match('/^[A-Z].+/', $name)) {
			return new $name();
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
				if(!isset($tmp_mached_list[$rule[1]])) {
					$tmp_mached_list[$rule[1]] = array();
				}
				$tmp_mached_list[$rule[1]][] = $rule;
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
				|| preg_match('/^'.str_replace('\/\/','\/.+?\/',str_replace('/','\/',preg_quote($value[0]))).'.*/',$_requri))) {
					$matched=$value;
					$longest_url=$value[0];
					$longest_url_length = strlen($longest_url);
				}
		}
		unset($matched[0]);
		unset($matched[1]);
		$matched=array_values($matched);
		$cache_ansver[$name] = $matched;
		$cache_longest_url_ansver[$name] = $longest_url;
		$this->_last_router_rule=$cache_longest_url_ansver[$name];
		return $matched;
	}
/* ================================================================================= */
	function shablonize($_str)
	{			return  preg_replace($this->template_patterns,$this->template_replacements,str_replace(array("\r\n","\r"),array("\n","\n"),$_str));	
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
	 
		if(!$ini=file($filename,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) return false;
		$res=array();
		$currentGroup='';
		$arrayKeys=array();
		foreach($ini as $row) {
			$first_symbol=substr($row,0,1);
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
		return d()->main();
	}
	function prepare_content($function,$content)
	{
		$this->_prepared_content[$function]=$content;
	}
}


/**
 * Автоматический создатель классов и загрузчик классов по спецификации PSR-0
 * Ищет файлы вида class_name.class.php, затем ищет классы в папке vendors по спецификации PSR-0.
 * Если ничего не найдено, создаёт класс и экземпляр класса ar (ActiveRecord)
 *
 * @param $class_name Имя класса
 */
function __autoload($class_name) {

	$class_name = ltrim($class_name, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strripos($class_name, '\\')) {
        $namespace = substr($class_name, 0, $lastNsPos);
        $class_name = substr($class_name, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
	$fileName = 'vendors'.DIRECTORY_SEPARATOR.$fileName;
	$lover_class_name=strtolower($class_name);

	if(file_exists(d()->php_files_list[$lover_class_name.'_class'])){
		require d()->php_files_list[$lover_class_name.'_class'];
	}elseif(file_exists($fileName)){
		require $fileName;
	}else{
		//Если совсем ничего не найдено, попытка использовать ActiveRecord.
		eval ("class ".$class_name." extends ActiveRecord {}");
	}

}
/**
* Функция, которая ничего не делает.
*/
function nothing()
{
	
}


if (!isset(doitClass::$instance)) {
	new doitClass();
}

/* END OF cms.php */