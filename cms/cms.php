<?php
/*
DoIt! CMS and VarVar framework
Copyright (C) 2011 Fahrutdinov Damir (aka Ainu)

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

0.11 ActiveRecord и foreach для объектов 07.08.2011 
0.7 переписано на ООП.
0.0 Нулевая версия do it CMS
	Рабочее название фреймворка Var(Var) Framework
	Система названа в честь статьи Variable Variables http://php.net/manual/en/language.variables.variable.php 26.01.2011
*/

session_start();
function url($param='', $length=1)
{	
	return d()->url($param,$length);
}
/**********************************************************************************************/
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

class doitClass
{
	private $fragmentslist=array(); //Массив кода фрагментов и шаблонов.
	private $replacements=array(); //Массив подмены шабонов при вызове
	private $caller=""; //Хранит название последней вызванной пользовательской функции.
	private $datapool=array(); //Большой массив всех опций, данных и переменных
	private $ini_database=array(); //Названия существующих ini-файлов, а также факт их использования
	private $sub_fragments=array(); //Подфрагменты, подготовыленные для использования
	private $where_is_sub_fragments=array(); //В каком родителе можно найти конкретный подфрагмент
	private $url_parts=array(); //Фрагменты url, разделённые знаком '/'
	private $call_chain=array(); //Цепь вызовов
	private $call_chain_current_link=array(); //Текущий элемент цепочки
	private $call_chain_level=0; //текущий уровень, стек для комманд
	public static $instance;
/* ================================================================================= */	
	function __construct()
	{
		self::$instance = $this;
		$_tmpurl=$_SERVER['REQUEST_URI'];
		$_wherequestionsign = strpos($_tmpurl,'?');
		if($_wherequestionsign !== false) {
			$_tmpurl = substr($_tmpurl, 0, $_wherequestionsign); //Обрезка GET-параметров
		}
		if(substr($_tmpurl,-1)=='/') {
			$_tmpurl=$_tmpurl."index";
		}
		$this->url_parts=explode('/',substr($_tmpurl,1));
		$dirlist=array('cms','app');
		$_fragmentslist=array();
		foreach($dirlist as $dirname) { //сначала инициализируются файлы из ./cms, затем из ./app
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
					$_fragmentname = str_replace('.','_',substr($_file,0,-5));
					if (substr($_fragmentname,0,1)=='_') {
						$_fragmentname=substr($_dir,5,-1).$_fragmentname;
					}
					if (substr($_file,-5)=='.html' && substr($_file,-9)!='.tpl.html') {
						$_fragmentname .= '_tpl';
					}
					if (substr($_file,-5)=='.html') {
						$_fragmentslist[$_fragmentname]=file_get_contents($dirname.$_dir.$_file);
					}
					//Модель - функции для работы с данными и бизнес-логика. Работа шаблонизатора подавлена.
					if (substr($_file,-9)=='.func.php') {
						include ($dirname.$_dir.$_file);
					}
					//Обработка факта наличия .ini-файлов
					if (substr($_file,-4)=='.ini') {
						//При первом запросе адрес сбрасывается в false для предотвращения последующего чтения
						//Хранит адрес ini-файла, запускаемого перед определённой функцией
						$this->ini_database[substr($_file,0,-4)]=$dirname.$_dir.$_file;
						//Правила, срабатывающие в любом случае, инициализация опций системы  и плагинов
						if (substr($_file,-8)=='init.ini') {
							$this->load_and_parse_ini_file ($dirname.$_dir.$_file);
						}
					}
				}
			}
		}
		$this->fragmentslist = $_fragmentslist;
		$_newfragmentist=array();
		//Поиск и объявление фрагментов, объявленных внутри шаблонов, подготовка массива с подфрагментами
		foreach($this->fragmentslist as $_parentfile=>$_text) {
			$_a1=array();
			$f=strpos($_text,'<fragment');
			while($f!==false) {
				$_a1[]=$f;
				$f=strpos($_text,'<fragment',$f+1);
			}
			$_a2=array();
			$f=strpos($_text,'</fragment');
			while($f!==false) {
				$_a2[]=$f;
				$f=strpos($_text,'</fragment',$f+1);
			}
			if (count($_a1)==0) continue;	
			if (count($_a1)!=count($_a2)) continue;
			while (count($_a1)>1) {
				$i=1;
				$founded=false;
				while (false == $founded && $i <= count($_a1)-1) {
					if($_a1[$i]>$_a2[$i-1]) {
						$res1=$_a1[0];
						$res2=$_a2[$i-1];
						unset($_a1[0]);
						unset($_a2[$i-1]);
						$_a1 = array_values ($_a1);
						$_a2 = array_values ($_a2);
						$founded=true;
					}
					$i++;
				}
				if ($founded == false) {
					$res1 = $_a1[0];
					$res2 = $_a2[$i - 1];
					unset($_a1[0]);
					unset($_a2[$i - 1]);
					$_a1 = array_values($_a1);
					$_a2 = array_values($_a2);
				}
				$res0=$res1+9;
				$res1=strpos($_text,'>',$res1)+1;
				$this->where_is_sub_fragments[trim(substr($_text,$res0,$res1-$res0-1))]=$_parentfile;
				$this->sub_fragments[$_parentfile][trim(substr($_text,$res0,$res1-$res0-1))]=$this->shablonize(substr($_text,$res1,$res2-$res1));
			}
			$res1=$_a1[0];
			$res2=$_a2[0];
			$res0=$res1+9;
			$res1=strpos($_text,'>',$res1)+1;
			$this->where_is_sub_fragments[trim(substr($_text,$res0,$res1-$res0-1))]=$_parentfile;
			$this->sub_fragments[$_parentfile][trim(substr($_text,$res0,$res1-$res0-1))]=$this->shablonize(substr($_text,$res1,$res2-$res1));
		}

	 	foreach ($this->fragmentslist as $_key=>$_value) { 
			$this->fragmentslist[$_key]=$this->shablonize($this->fragmentslist[$_key]);
		}
		
		//Обработка actions. Ничего не выводится.
		if(isset($_POST) && isset($_POST['_action'])) {
			if($this->validate_action($_POST['_action'],$_POST[$_POST['_element']])) {
				$this->{'action_'.$_POST['_action']}($_POST[$_POST['_element']]); 
			}
		}
		
	}
	
	
/* ================================================================================= */	
	public function validate_action($validator_name,$params) //todo: сделать пользователезаменяемой штукой
	{
		$rules=$this->datapool['validator'][$validator_name];
		if(!isset($this->datapool['notice'])) {
			$this->datapool['notice']=array();
		}
		$is_ok=true;
		
		foreach($rules as $key=>$value) {
			if(isset($value['required']) && (!isset ($params[$key]) || $params[$key]=='')) {
				$this->datapool['notice'][] = $value['required']['message'];
				$is_ok=false;
			}
		}		
		
		return $is_ok;
	}
/* ================================================================================= */	
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
	public function __call($name, $arguments)
	{
		//Одиночная загрузка .ini файла при первом обращении к функции
		//Также мы можем вручную привязать ini-файл к любой функции/шаблону
		if (isset($this->ini_database[$name])) {

			$this->load_and_parse_ini_file($this->ini_database[$name]);
			unset ($this->ini_database[$name]);
		}
		 
		if (count($arguments)!=0 && is_array($arguments[0])) {
			foreach($arguments[0] as $key=>$value) {
				$this->datapool[$key]=$value;
			}
		}
		$_result_end='';
		$_newnames = $this->get_function_alias($name);
		$_currentname=$name;
		$_continuechain = true;
		for($i=0;$i<=count($_newnames)-1;$i++){
		$_newname = $_newnames[$i];
			$name=$_currentname;
			if($name!=$_newname) {
				if (isset($this->ini_database[$_newname])) {
					$this->load_and_parse_ini_file($this->ini_database[$_newname]);
					unset ($this->ini_database[$_newname]);
				}
			}
			$name=$_newname;
			//Проверка на существование фрагмента fragment_tpl, если самой функции нет
			if ( (!function_exists($name)) && (isset( $this->fragmentslist[$name."_tpl"]))) { 
				$name = $name."_tpl";
			}
			//Активация подфрагментов перед вызовом родительской функции
			if (isset($this->sub_fragments[$name])) {
				foreach($this->sub_fragments[$name] as $_key=>$_value) {	
					$this->fragmentslist[$_key]=$_value;
				}
			}
			//TODO: Отказаться от субфрагментов в пользу helper-ов
			//Если в дальнейшем ожидается ошибка по причине вызова внешнего фрагмента, провести его инициацию
			if(!isset($this->fragmentslist[$name]) && isset($this->where_is_sub_fragments[$name])) {
				$this->fragmentslist[$name]= $this->sub_fragments[$this->where_is_sub_fragments[$name]][$name];
			}
			
			$this->call_chain_level++; //поднимаем уровень текущего стека очереди
			//Сохраняем текущую цепочку команд
			$this->call_chain[$this->call_chain_level] = $_newnames;
			$this->call_chain_current_link[$this->call_chain_level]=$i;
			
			//Тут вызываются предопределённые и пользовательские функции
			ob_start();
			if (function_exists($name)) {
				$_executionResult=call_user_func_array($name, $arguments);
			}else{
				$_executionResult=eval('?'.'>'.$this->fragmentslist[$name].'<'.'?php ;');
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
			if (count($_newnames)==1){
				return $_end;
			} else {
				$_result_end .= $_end;
			}
		}
		return $_result_end;
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
		
		//Проверка префиксов
		foreach ($this->prefixes as $_one_prefix) {
			if(preg_match($_one_prefix[0], $name)) {
				return $this->{$_one_prefix[1]}($name);
			}
		}
		//$this->caller=$name;
		return '';
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
	//Проверяет URL и анализирует текущий массив, при подходящем, возвращает псевдоним
	function get_function_alias($name)
	{
		static $url_list_size = 0;
		$_matches=array();
		$matched=array('','',$name);
		$longest_url='';
		$ruleslist = $this->datapool['urls'];
		$_requri = '/'.$this->url();
		//Определение наиболее подхходящего правила в списке правил роутинга. Наиболее длинное из подходящих - приоритетнее.
		foreach($ruleslist as $key=>$value) {			
			//TODO: документация к следующей конструкции
			if(( $value[1] == $name && ($_requri==$value[0] || substr($_requri,0,strlen($value[0]))==$value[0] || preg_match('/^'.str_replace('\/\/','\/.+?\/',str_replace('/','\/',preg_quote($value[0]))).'.*/',$_requri))) && (strlen($value[0]) > strlen($longest_url))) {
				$matched=$value;
				$longest_url=$value[0];
			}
		}
		unset($matched[0]);
		unset($matched[1]);
		$matched=array_values($matched);
		return $matched;
	}
/* ================================================================================= */
	function shablonize($_str)
	{
	
		$_str=preg_replace('/<fragment\s+([a-zA-Z0-9_]+)>/','<'.'?php $tmparr= $this->$1;
if(is_string($tmparr) || (is_array($tmparr) &&  !array_key_exists(0,$tmparr))) $tmparr=array($tmparr);
foreach($tmparr as $key=>$subval)
	if(is_string($subval)) print $subval;else {
		$this->datapool["override"]="";
		foreach($subval as $subkey=>$subvalue) $this->datapool[$subkey]=$subvalue; 
		if ($this->datapool["override"]!="") { print $this->{$this->datapool["override"]}(); } else { ?'.'>',$_str);
		
		
		$_str=preg_replace('/<foreach\s+(.*?)\s+as\s+([a-zA-Z0-9_]+)>/','<'.'?php $tmparr= $this->$1;
if(is_string($tmparr) || (is_array($tmparr) &&  !array_key_exists(0,$tmparr))) $tmparr=array($tmparr);
foreach($tmparr as $key=>$subval)
	if(is_string($subval)) print $subval;else {
		$this->datapool["override"]="";
		if(is_object($subval)){
			 $this->datapool[\'$2\']=$subval; 
			 $this->datapool[\'override\']=$subval->override; 
		}else{
			foreach($subval as $subkey=>$subvalue) $this->datapool[\'$2\'][$subkey]=$subvalue; 
		}
		if ($this->datapool["override"]!="") { print $this->{$this->datapool["override"]}(); } else { ?'.'>',$_str);
		
		//TODO: приписать if (is_object($tmparr)) $Tmparr=array($tmparr)
		$_str=preg_replace('/<foreach\s+(.*?)>/','<'.'?php $tmparr= $this->$1;
if(is_string($tmparr) || (is_array($tmparr) &&  !array_key_exists(0,$tmparr))) $tmparr=array($tmparr);
foreach($tmparr as $key=>$subval)
	if(is_string($subval)) print $subval;else {
		$this->datapool["override"]="";
		foreach($subval as $subkey=>$subvalue) $this->datapool[$subkey]=$subvalue; 
		if ($this->datapool["override"]!="") { print $this->{$this->datapool["override"]}(); } else { ?'.'>',$_str);
	
		$_str=preg_replace('/<type\s+([a-zA-Z0-9_-]+)>/','<'.'?php if($this->type=="$1"){ ?'.'>',$_str);
		$_str=str_replace('</fragment>','<'.'?php } } ?'.'>',$_str);
		$_str=str_replace('</foreach>' ,'<'.'?php } } ?'.'>',$_str);
		$_str=str_replace('</type>','<'.'?php } ?'.'>',$_str);	
		$_str=str_replace('</hidden>','<'.'?php } ?'.'>',$_str);
		$_str=str_replace('<hidden>','<'.'?php if(false){ ?'.'>',$_str);
		$_str=preg_replace('/\{{\/([a-zA-Z0-9_]+)\}}/','</$1>',$_str);//Синтаксический сахар
		$_str=preg_replace('/\{{([a-zA-Z0-9_]+)\}}/','<'.'?php print $this->$1(); ?'.'>',$_str);
		$_str=preg_replace('/\{{([a-zA-Z0-9_]+)\s+(.*?)\}}/', '<'.'?php print $this->$1(array($2)); ?'.'>',$_str);
		
		$_str=preg_replace('/\{([a-zA-Z0-9_]+)\}/','<'.'?php print  $this->$1; ?'.'>',$_str);
		$_str=preg_replace('/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\}/','<'.'?php if(is_array($this->$1)) {  print  $this->$1[\'$2\'];
		}else{ print  $this->$1->$2; } ?'.'>',$_str);
		
		/* FIXME:Бяка*/
		$_str=preg_replace('/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+).([a-zA-Z0-9_]+)\}/','<'.'?php print  $this->$1->$2->$3; ?'.'>',$_str);
		$_str=preg_replace('/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+).([a-zA-Z0-9_]+).([a-zA-Z0-9_]+)\}/','<'.'?php print  $this->$1->$2->$3->$4; ?'.'>',$_str);
		$_str=preg_replace('/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+).([a-zA-Z0-9_]+).([a-zA-Z0-9_]+).([a-zA-Z0-9_]+)\}/','<'.'?php print  $this->$1->$2->$3->$4->$5; ?'.'>',$_str);
		/* /FIXME:Бяка*/
		return  $_str;
	}
/* ============================================================================== */

	//получение данных из .ini файла
	function load_and_parse_ini_file($filename){
	
		$res=array();
		if(!$ini=file_get_contents($filename)) return false;
		$ini=explode("\n",$ini);
		$currentGroup='';
		$arrayKeys=array();
		foreach($ini as $row) {
			$row=trim($row);
			if($row=='')continue; //Пустые строки игнорируются
			if (substr($row,0,1)==';') continue; //Комментарий
			if (substr($row,0,1)=='[') { //Начало новой группы [group]
				$currentGroup=substr($row,1,-1);		
				continue;
			}
			$delimeterPos=strpos($row,'=');
			if($delimeterPos===false) {
				//Если тип строки - неименованный массив, разделённый пробелами
				$subject=$currentGroup;
				$row=str_replace("\t",' ',$row);
				$tmparr = explode(' ',$row);
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
				$value=array($arrayKeys[$currentGroup]=>$value);
				$arrayKeys[$currentGroup]++; //Генерация номера элемента массива, массив нельзя перемешивать с обычными данными
			} else {
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
