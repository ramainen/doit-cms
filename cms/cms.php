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

0.14 Вывод orm объектов (ar->show(), print User). Поиск по Url (Page->about->title). Удаление. Базовый полиморфизм.
0.11 ActiveRecord и foreach для объектов 07.08.2011 
0.7 переписано на ООП.
0.0 Нулевая версия DoIt CMS
	Рабочее название фреймворка Var(Var) Framework
	Система названа в честь статьи Variable Variables http://php.net/manual/en/language.variables.variable.php 26.01.2011
*/

session_start();
function url($param='', $length=1)
{	
	return d()->url($param,$length);
}

function doit($object='')
{
	if (!isset(doitClass::$instance)) {
		new doitClass();
	}
	if($object=='') {
		return doitClass::$instance; 
	}
	return doitClass::$instance->$object;
}
//Псевдоним для более быстрого доступа
function d($object='')
{
	if (!isset(doitClass::$instance)) {
		new doitClass();
	}
	if($object=='') {
		return doitClass::$instance;
	}
	return doitClass::$instance->$object;
}
// Запуск валидатора и обработка действий
function action()
{
	$paramaters = func_get_args();
	return call_user_func_array(array(d(),'action'),  $paramaters);
}
class doitClass
{
	public $datapool=array(); //Большой массив всех опций, данных и переменных, для быстрого прямого доступа доступен публично
	public static $instance;
	
	private $fragmentslist=array(); //Массив кода фрагментов и шаблонов.
	private $php_files_list=array(); //Массив найденных php файлов.
	private $ini_database=array(); //Названия существующих ini-файлов, а также факт их использования
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

/* ================================================================================= */	
	function __construct()
	{
		self::$instance = $this;
		// Массив для шаблонизатора
		
		// <foreach users as user>
		$this->template_patterns[]=	'/<foreach\s+(.*?)\s+as\s+([a-zA-Z0-9_]+)>/';
		$this->template_replacements[]='<'.'?php $tmparr= $this->$1;
		if(is_object($tmparr)) { $tmparr = $tmparr->all;}
if(is_string($tmparr) || (is_array($tmparr) && (count($tmparr)!=0) && !array_key_exists(0,$tmparr))) $tmparr=array($tmparr);
foreach($tmparr as $key=>$subval)
	if(is_string($subval)) print $subval;else {
		$this->datapool["override"]="";
		if(is_object($subval)){
			 $this->datapool[\'$2\']=$subval; 
			 $this->datapool[\'this\']=$subval; 
			 $this->datapool[\'override\']=$subval->override; 
		}else{
		$this->datapool[\'this\']=array();
		foreach($subval as $subkey=>$subvalue) { 
		$this->datapool[\'$2\'][$subkey]=$subvalue;
		$this->datapool[\'this\'][$subkey]=$subvalue;
		}   }
		if ($this->datapool["override"]!="") { print $this->{$this->datapool["override"]}(); } else { ?'.'>';

		
		//TODO: приписать if (is_object($tmparr)) $Tmparr=array($tmparr)
		// TODO: 		foreach($subval as $subkey=>$subvalue) $this->datapool[$subkey]=$subvalue; 
		//	возможно, убрать эту конструкцию	
		
		// <foreach users>
		$this->template_patterns[]='/<foreach\s+(.*?)>/';
		$this->template_replacements[]='<'.'?php $tmparr= $this->$1;
		if(is_object($tmparr)) {$tmparr = $tmparr->all;}
if(is_string($tmparr) || (is_array($tmparr) && (count($tmparr)!=0) && !array_key_exists(0,$tmparr))) $tmparr=array($tmparr);
foreach($tmparr as $key=>$subval)
	if(is_string($subval)) print $subval;else {
		$this->datapool["override"]="";
		foreach($subval as $subkey=>$subvalue) $this->datapool[$subkey]=$subvalue; 
		if ($this->datapool["override"]!="") { print $this->{$this->datapool["override"]}(); } else { ?'.'>';
	
		// {{{content}}}
		$this->template_patterns[]='/\{{{([#a-zA-Z0-9_]+)\}}}/';
		$this->template_replacements[]='<'.'?php print $this->render("$1"); ?'.'>'; 
		
		// <type admin>
		$this->template_patterns[]='/<type\s+([a-zA-Z0-9_-]+)>/';
		$this->template_replacements[]='<'.'?php if($this->type=="$1"){ ?'.'>';
				
		// <content for header>
		$this->template_patterns[]='/<content\s+for\s+([a-zA-Z0-9_-]+)>/';
		$this->template_replacements[]='<'.'?php ob_start(); $this->datapool["current_ob_content_for"] = "$1"; ?'.'>';
		
		// </content>
		$this->template_patterns[]='/<\/content>/';
		$this->template_replacements[]='<'.'?php  $this->datapool[$this->datapool["current_ob_content_for"]] = ob_get_contents(); ob_end_clean(); ?'.'>';
		
		// </foreach>
		$this->template_patterns[]='/<\/foreach>/' ;
		$this->template_replacements[]='<'.'?php } } ?'.'>';
		
		// </type>
		$this->template_patterns[]='/<\/type>/';
		$this->template_replacements[]='<'.'?php } ?'.'>';
		
		// {{/form}}
		$this->template_patterns[]='/\{{\/([a-zA-Z0-9_]+)\}}/';
		$this->template_replacements[]='</$1>';//Синтаксический сахар
		
		// {{content}}
		$this->template_patterns[]='/\{{([#a-zA-Z0-9_]+)\}}/';
		$this->template_replacements[]='<'.'?php print $this->call("$1"); ?'.'>';
		
		// {{helper param}}
		$this->template_patterns[]='/\{{([#a-zA-Z0-9_]+)\s+([a-zA-Z0-9_]+)\}}/';
		$this->template_replacements[]= '<'.'?php print $this->call("$1", array(d()->$2));  ?'.'>';
		
		// {{helper 'parame','param2'=>'any'}}
		$this->template_patterns[]='/\{{([#a-zA-Z0-9_]+)\s+(.*?)\}}/';
		$this->template_replacements[]='<'.'?php print $this->call("$1",array(array($2))); ?'.'>';
		
		// {title}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php print  $this->$1; ?'.'>';
		
		// {:title}
		$this->template_patterns[]='/\{:([a-zA-Z0-9\._]+)\}/';
		$this->template_replacements[]='<'.'?php } ?'.'>';
		
		// {title:}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+):\}/';
		$this->template_replacements[]='<'.'?php if($this->$1) { ?'.'>';
		
		// {page.title}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php if(is_array($this->$1)) {  print  $this->$1[\'$2\']; }else{ print  $this->$1->$2; } ?'.'>';
		
		// {page.title:}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+):\}/';
		$this->template_replacements[]='<'.'?php if((is_array($this->$1) && $this->$1[\'$2\']) || $this->$1->$2) { ?'.'>';
		
		// {.title}
		$this->template_patterns[]='/\{\.([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php if(is_array($this->this)) {  print  $this->this[\'$1\']; }else{ print  $this->this->$1; } ?'.'>';
		
		// {title|h}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\|([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php print  $this->$2($this->$1); ?'.'>';
		
		// {page.title|h}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\|([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php if(is_array($this->$1)) {  print  $this->$3($this->$1[\'$2\']); }else{ print  $this->$3($this->$1->$2); } ?'.'>';
		
		// {page.user.title}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+).([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php print  $this->$1->$2->$3; ?'.'>';
		
		// {page.parent.user.title}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+).([a-zA-Z0-9_]+).([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php print  $this->$1->$2->$3->$4; ?'.'>';
		
		// {page.parent.user.avatar.url}
		$this->template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+).([a-zA-Z0-9_]+).([a-zA-Z0-9_]+).([a-zA-Z0-9_]+)\}/';
		$this->template_replacements[]='<'.'?php print  $this->$1->$2->$3->$4->$5; ?'.'>';

		//Обрезка GET-параметров
		$_tmpurl=urldecode($_SERVER['REQUEST_URI']);
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
		
		//сначала инициализируются файлы из ./cms, затем из ./app
		foreach(array('cms','app') as $dirname) { 
			$_handle = opendir($dirname);
			$_files=array();
			$_files['/']=array();
			while (false !== ($_file = readdir($_handle))) {
				 if(substr($_file,0,4)=='mod_') {
					$_subhandle = opendir($dirname.'/'.$_file);
					$_files['/'.$_file.'/']=array();
					while (false !== ($_subfile = readdir($_subhandle))) {
						$_files['/'.$_file.'/'][]=$_subfile;
					}
					closedir($_subhandle);
				 } else {
					$_files['/'][]=$_file;
				 }
			}
			closedir($_handle);
			
			foreach($_files as $_dir => $_subfiles) {
				foreach($_subfiles as $_file) {
					if (substr($_file,-5)=='.html') {
						$_fragmentname = str_replace('.','_',substr($_file,0,-5));
					} else {
						$_fragmentname = str_replace('.','_',substr($_file,0,-4));
					}
					if (substr($_fragmentname,0,1)=='_') {
						$_fragmentname=substr($_dir,5,-1).$_fragmentname;
					}
					if (substr($_file,-5)=='.html') {
						if (substr($_file,-9)!='.tpl.html') {
							$_fragmentname .= '_tpl';
						}	
						$this->fragmentslist[$_fragmentname] = $dirname.$_dir.$_file;
						continue;
					}
					
					//Контроллер - функции для работы с данными и бизнес-логика. Работа шаблонизатора подавлена.
					if (substr($_file,-9)=='.func.php') {
						include ($dirname.$_dir.$_file);
						continue;
					}
					if (substr($_file,-4)=='.php') {
						$this->php_files_list[$_fragmentname] = $dirname.$_dir.$_file;
						continue;
					}
					
					//Обработка факта наличия .ini-файлов
					if (substr($_file,-4)=='.ini') {
						//Правила, срабатывающие в любом случае, инициализация опций системы  и плагинов
						if (substr($_file,-8)=='init.ini') {
							//Если имя файла оканчивается на .init.ini, инициализировать его сразу
							$this->load_and_parse_ini_file ($dirname.$_dir.$_file);
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

	}

/* ================================================================================= */	
  //Проверяет параметры в соотвествии с правилами, в случае ошибки возвращает false
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
/* ================================================================================= */	
	public function add_notice($text)
	{
		$this->datapool['notice'][] = $text;
	}	
/* ================================================================================= */	
	public function action($action_name)
	{
		//Обработка actions. Ничего не выводится.
		// TODO: А зачем тут func_get_args, забыл.
		// TODO: Ага, чтобы передавать в action(дополнительныефункции для проверки)
		$parameters = func_get_args();
		if(isset($_POST) && isset($_POST['_action']) && ($action_name == $_POST['_action']) && ($this->validate_action($_POST['_action'], $_POST[$_POST['_element']],$parameters ))) {
			$this->datapool['params'] =  $_POST[$_POST['_element']];
			return $this->call($_POST['_action'],array($_POST[$_POST['_element']]));
		}
	}
	/* ================================================================================= */
// Аналог action(), но только возвращающий true или false,для простой валидации
	public function validate($action_name)
	{
		$parameters = func_get_args();
		if(isset($_POST) && isset($_POST['_action']) && ($action_name == $_POST['_action']) && ($this->validate_action($_POST['_action'], $_POST[$_POST['_element']],$parameters ))) {
			$this->datapool['params'] =  $_POST[$_POST['_element']];
			return true;
		}
		return false;
	}
/* ================================================================================= */	
//TODO: оптимизировать функцию url как частозапускаемую
	public function url($param='',$length=1)
	{
		if($param=='') {
			$param = 1;
			$length = count($this->url_parts);
		}
		if($length<=0) {
			$length = count($this->url_parts) + $length - 1;
		}
		if(!is_numeric($param)) { //url('users')
			$readyindex=false;
			$i=0;
			foreach ($this->url_parts as $key => $value) {
				$i++;
				if($key==$param) {
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
/* ================================================================================= */	
	public function call($name, $arguments=array())
	{
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



			ob_start();
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
			} elseif(isset($this->php_files_list[$name])){
				include ($this->php_files_list[$name]);
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

					$_classname=substr($name,0,$_fsym).'_controller';
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

					$_executionResult=call_user_func_array(array($this->universal_controller_factory($_classname), $_methodname), $arguments);
				} else {
					$_executionResult=eval('?'.'>'.$this->get_compiled_code($name).'<'.'?php ;');
				}
			}
			$_end = ob_get_contents();
			ob_end_clean();
			
			if (!is_null($_executionResult)) {
				$_end = $_executionResult;
			}
			//Загружаем актуальную цепочку команд. call_chain могла измениться
			$_newnames = $this->call_chain[$this->call_chain_level];
			$i = $this->call_chain_current_link[$this->call_chain_level];
			$this->call_chain_level--; //опускаем уровень текущего стека очереди
			if (count($_newnames)==1) {
				return $_end;
			} else {
				$_result_end .= $_end;
			}
		}
		return $_result_end;
	}
/* ================================================================================= */
	//ленивая загрузка и шаблонизация
	function get_compiled_code($fragmentname)
	{
		if(!isset ($this->compiled_fragments[$fragmentname])) {
			$this->compiled_fragments[$fragmentname]=$this->shablonize(file_get_contents($this->fragmentslist[$fragmentname]));
		}
		return $this->compiled_fragments[$fragmentname];
	}
/* ================================================================================= */	
	//вызов call(), эта функция более гибкая, и умеет выполнять запросы вроде call('clients#show');
	public function __call($name, $arguments)
	{
		return 	$this->call($name, $arguments);
	}
/* ================================================================================= */	
	//Фабрика экземпляров контроллеров
	//universal_controller_factory('clients_controller') вернёт существующий экземпляр класса clients_controller, или создаст его и вернёт.
	public function universal_controller_factory($name)
	{
		static $controllers =array(); //Склад контроллеров
		if (! isset ($controllers[$name])) {
			$controllers[$name] = new  $name();
		}
		return $controllers[$name];
	}
/* ================================================================================= */	
	function __set($name,$value)
	{
		$this->datapool[$name]=$value;
	}
/* ================================================================================= */	
	function __get($name)
	{
		if(isset($this->datapool[$name])) {
			return $this->datapool[$name];
		}
		if(substr($name,-11)=='_controller') {
			return  doit_caller_factory($name);
		}
		//Проверка префиксов для модулей для модулей и расширений
		//TODO: это слишком медленно
		foreach ($this->datapool['prefixes'] as $_one_prefix) {
			if(preg_match($_one_prefix[0], $name)) {
				return $this->{$_one_prefix[1]}($name);
			}
		}
		return '';
	}

/* ================================================================================= */
	//Запускает имя_функции.tpl.html, либо пытается угадать имя текущей триады
	public function view($parent_function=false)
	{
		//Определяем функцию (контроллер), из которого был произведён вызов. Припиываем _tpl, вызываем
		if($parent_function===false) {
			$parent_function =  $this->call_chain[$this->call_chain_level][$this->call_chain_current_link[$this->call_chain_level]];
		}

		if(substr($parent_function,-4)!='_tpl'){
			$parent_function .= '_tpl';
		}
		$parent_function =  str_replace('#','_',$parent_function);
		return $this->call($parent_function);
	}
/* ================================================================================= */
	//Меняет следующий элемент в очереди-цепи
	public function set_next_chain($chainname)
	{
		$this->call_chain[$this->call_chain_level][$this->call_chain_current_link[$this->call_chain_level]+1] = $chainname;
	}
/* ================================================================================= */
	//Останавливает всю следующую цепочку
	public function stop_next_chains()
	{
		$this->call_chain_current_link[$this->call_chain_level] = count($this->call_chain[$this->call_chain_level])+1;
	}
/* ================================================================================= */
	//Вставляет элемент в цепь после текущего
	public function insert_next_chain($chainname)
	{
		//$this->call_chain[$this->call_chain_level][$this->call_chain_current_link[$this->call_chain_level]+1] = $chainname;
	}
/* ================================================================================= */	
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
/* ================================================================================= */
	//Проверяет URL и анализирует текущий массив правил, при наличии подходящего, возвращает массив всевдонимов (цепочку)
	function get_function_alias($name)
	{
		static $cache_ansver=array(); //Кеш ответов для быстрого реагирования
		static $cache_longest_url_ansver=array(); //Кеш ответов для быстрого реагирования
		static $rules_array = false; //Ассоциативный массив правил для того, чтобы не опрашивать весь список
		
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
	{
		return  preg_replace($this->template_patterns,$this->template_replacements,$_str);	
	}
/* ================================================================================= */
/*
Выводит значение переменной, либо каждое из значений массива, либо запускает одноимённую функцию.
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
/* ============================================================================== */
	//получение данных из .ini файла
	function load_and_parse_ini_file($filename){
		if(!$ini=file($filename)) return false;
		$res=array();
		$currentGroup='';
		$arrayKeys=array();
		foreach($ini as $row) {
			$row=rtrim($row);
			if($row=='' || substr($row,0,1)==';') continue; //Пустые строки игнорируются
			if (substr($row,0,1)=='[') { //Начало новой группы [group]
				$currentGroup=substr($row,1,-1);
				continue;
			}
			if(preg_match('/^[a-zA-Z0-9_\.]+\s*\=/',$row)!=1) {
				
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
				$value2=$value;
				foreach($value as $number => $element) {
					if(substr($element,-1,1)==':') {
						$value2[substr($element,0,-1)] = $value[$number+1];
						unset($value2[$number]);
						unset($value2[$number+1]);						
					}
				}
				
				$value=array($arrayKeys[$currentGroup]=>$value2);
				$arrayKeys[$currentGroup]++; //Генерация номера элемента массива, массив нельзя перемешивать с обычными данными
			} else {
				$delimeterPos=strpos($row,'=');
				if (substr($row,0,1)=='$') { //Если опция начинается на "$", то её значение - выражение на PHP (например, дата или md5-хеш).
					$subject= rtrim(substr($row,1,$delimeterPos-1));
					if ($currentGroup!='') {
						$subject = $currentGroup . '.' . $subject;
					}
					$runstr=ltrim(substr($row,$delimeterPos+1));
					eval('$value='.$runstr.';');
				} else {		
					$subject= rtrim(substr($row,0,$delimeterPos));
					if ($currentGroup!='') {
						$subject = $currentGroup . '.' . $subject;
					}
					$value=ltrim(substr($row,$delimeterPos+1));
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
/* ============================================================================== */	
}
/*
фабрика и хранилище для doitCaller
при запросе doit_caller_factory('clients_controller') создаёт (если нет) и возвращает экземпляр 
прокси - объекта doitCaller, способного выполнять методы.
*/
function doit_caller_factory($controllername)
{
	static $callers = array();
	if(!isset ($callers[$controllername])) {
		$callers[$controllername] = new doitCaller($controllername);
	}
	return $callers[$controllername];
}

/*
класс doitCaller создаёт универсальный прокси-объект. При вызове его метода  вызов передаётся в основной объект системы.
конструкция $caller=doitCaller('clients_controller'); $caller->show();
перенаправит вызов универсальному запускателю d()->call('clients#show');
doitCaller использует основной объект системы при попытке получить переменную - объект d()->*_controller 

таким образом, запросы вида  d()->clients_controller->show(); перенаправляются в  d()->call('clients#show');
Это позволяет при необходимости переопределять поведение при помощи роутера.
*/
class doitCaller
{
	private $_classname;
	function __call($name,$params)
	{
		return d()->call($this->_classname."#".$name,$params);
	}
	function __construct($controllername) {
		$this->_classname = substr($controllername,0,-11);
	}
	function __get ($name) {
		return d()->universal_controller_factory($this->_classname.'_controller')->$name;
	}
	function __set ($name,$value) {
		d()->universal_controller_factory($this->_classname.'_controller')->$name = $value;
	}
}

// Автоматический создатель классов и загрузчик классов по спецификации PSR-0
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
	if(file_exists($fileName)){
		require $fileName;	
	}else{
		//Если совсем ничего не найдено, попытка использовать ActiveRecord.
		eval ("class ".$class_name." extends ar {}");
	}

}
