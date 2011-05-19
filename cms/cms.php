<?php
/*

DoIt! CMS and VarVar framework
Copyright (C) 2011 Fahrutdinov Ainu Damir

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



0.9 *.ini файлы с данными и опциями.
0.8 *.func.{html|php} - прослойка с моделью, не шаблонизируется, не eval-ится
0.7 переписано на ООП.
...
0.2 фрагменты как шаблоны. Вложенные фрагменты. 06.02.2011
0.1 работающий шаблонизатор. Умеет выводить переменные, фрагменты со значениями по-умолчанию, списки,
	шаблонизировать внутри списков. 31.01.2011
0.0 Нулевая версия do it CMS
	Рабочее название фреймворка Var(Var) Framework
	Система названа в честь статьи Variable Variables http://php.net/manual/en/language.variables.variable.php 26.01.2011
  
Ключевые переменные:
$_URL			Массив фрагментов пути, включающий в себя названия директорий и конечный файл


*/
//FIXME: бяка Определение текущего url
session_start();
$_URL=$_SERVER['REQUEST_URI'];
if(substr($_URL,-1)=='/')$_URL=$_URL."index";
if(strpos($_URL,'?')!==false)$_URL=substr($_URL,0,strpos($_URL,'?'));
$_URL=explode('/',substr($_URL,1));
$GLOBALS['_URL']=$_URL; //Для того случая, если текущий контекст выполняется изнутри другий функции

function doit()
{
	if (!isset(doitClass::$instance)) {
		new doitClass();
	}
	return doitClass::$instance;
}

class doitClass
{
	private $fragmentslist=array(); //Массив кода фрагментов и шаблонов.
	private $replacements=array(); //Массив подмены шабонов при вызове
	private $caller=""; //Хранит название последней вызванной пользовательской функции.
	private $datapool=array(); //Большой массив всех опций, данных и переменных
	private $iniDatabase=array(); //Названия существующих ini-файлов, а также факт их использования
	private $subFragments=array(); //Подфрагменты, подготовыленные для использования
	private $whereIsSubFragments=array(); //В каком родителе можно найти конкретный подфрагмент
	public static $instance;
/* ================================================================================= */	
	function __construct()
	{
		self::$instance = $this;
		$_fragmentslist=array();
		$_handle = opendir('tpl');
		$_files=array();
		$_files['/']=array();
		while (false !== ($_file = readdir($_handle))) {
			 if(substr($_file,0,4)=='mod_')
			 {
				$_subhandle = opendir('tpl/'.$_file);
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
				if (substr($_file,-5)=='.html' && substr($_file,-10)!='.func.html') {
					$_fragmentslist[str_replace('.','_',substr($_file,0,-5))]=file_get_contents('tpl'.$_dir.$_file);
				}
				//Модель - функции для работы с данными и бизнес-логика. Работа шаблонизатора подавлена.
				if (substr($_file,-10)=='.func.html' || substr($_file,-9)=='.func.php') {
					include ('tpl'.$_dir.$_file);
				}
				//Обработка факта наличия .ini-файлов
				if (substr($_file,-4)=='.ini') {
					//При первом запросе адрес сбрасывается в false для предотвращения последующего чтения
					//Хранит адрес ini-файла, запускаемого перед определённой функцией
					$this->iniDatabase[substr($_file,0,-4)]='tpl'.$_dir.$_file;
					//Правила, срабатывающие в любом случае, инициализация опций системы  и плагинов
					if (substr($_file,-8)=='init.ini') {
						$this->loadAndParseIniFile ('tpl'.$_dir.$_file);
					}
				}
			}
		}
		
		$this->fragmentslist = $_fragmentslist;
		$_newfragmentist=array();
		//Поиск и объявление фрагментов, объявленных внутри шаблонов, подготовка массива с подфрагментами
		foreach($this->fragmentslist as $_key=>$_value) {
			$this->parsesubfragments($_value,$_newfragmentist,$_key);
		}

	 	foreach ($this->fragmentslist as $_key=>$_value) { 
			$this->fragmentslist[$_key]=$this->shablonize($this->fragmentslist[$_key]);
		}
	 
	}
/* ================================================================================= */	
	public function __call($name, $arguments)
	{
		global $_URL; //FIXME: нахрен GLOBAL
		
		//Одиночная загрузка .ini файла при первом обращении к функции
		//Также мы можем вручную привязать ini-файл к любой функции/шаблону
		if (isset($this->iniDatabase[$name])) {
			$this->loadAndParseIniFile($this->iniDatabase[$name]);
			unset ($this->iniDatabase[$name]);
		}
		
		if (count($arguments)!=0) {
			foreach($arguments[0] as $key=>$value) {
				$this->datapool[$key]=$value;
			}
		}
		
		$_newname = $this->getFunctionAlias($name);
		if($name!=$_newname) {
			if (isset($this->iniDatabase[$_newname])) {
				$this->loadAndParseIniFile($this->iniDatabase[$_newname]);
				unset ($this->iniDatabase[$_newname]);
			}
		}
		$name=$_newname;
		//Проверка на существование fragment_tpl, если в вызове есть параметры
		if ((count($arguments)!=0) && (count($arguments[0])!=0) && (isset( $this->fragmentslist[$name."_tpl"]))) { 
			$name = $name."_tpl";
		}
		//Активация подфрагментов перед вызовом родительской функции
		if (isset($this->subFragments[$name])) {
			foreach($this->subFragments[$name] as $_key=>$_value) {	
				$this->fragmentslist[$_key]=$_value;
			}
		}
		
		//Если в дальнейшем ожидается ошибка по причине вызова внешнего фрагмента, провести его инициацию
		if(!isset($this->fragmentslist[$name]) && isset($this->whereIsSubFragments[$name])) {
			$this->fragmentslist[$name]= $this->subFragments[$this->whereIsSubFragments[$name]][$name];
		}
		//Тут вызываются предопределённые и пользовательские функции
		ob_start();
		if (function_exists($name)) {
			$_executionResult=call_user_func($name, $arguments);
		}else{
			$_executionResult=eval('?'.'>'.$this->fragmentslist[$name].'<'.'?php ;');
		}
		$_end = ob_get_contents();
		ob_end_clean();
		
		if (!is_null($_executionResult)) {
			$_end = $_executionResult;
		}
		
		return $_end;
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
		//$this->caller=$name;
		return '';
	}
/* ================================================================================= */
	//Проверяет URL и анализирует текущий массив, при подходящем, возвращает псевдоним
	function getFunctionAlias($name)
	{
		global $_URL;
		static $url_list_size;
		if (!isset($url_list_size)) {
			$url_list_size = 0;
		}
		$_matches=array();
		$matched=$name;
		$longest_url='';
		$ruleslist = $this->datapool['urls'];
		foreach($ruleslist as $key=>$value) {
			if($value[1] == $name && ($_SERVER['REQUEST_URI']==$value[0] || substr($_SERVER['REQUEST_URI'],0,strlen($value[0]))==$value[0] || preg_match('/'.str_replace('//','\/.+?\/',str_replace('/','\/',preg_quote($value[0]))).'.*/',$_SERVER['REQUEST_URI']))) {
				if(strlen($value[0]) > strlen($longest_url)) {
					$matched=$value[2];
					$longest_url=$value[0];
				}
			}
		}
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
		
		$_str=preg_replace('/<foreach\s+(.*)>/','<'.'?php $tmparr= $this->$1;
if(is_string($tmparr) || (is_array($tmparr) &&  !array_key_exists(0,$tmparr))) $tmparr=array($tmparr);
foreach($tmparr as $key=>$subval)
	if(is_string($subval)) print $subval;else {
		$this->datapool["override"]="";
		foreach($subval as $subkey=>$subvalue) $this->datapool[$subkey]=$subvalue; 
		if ($this->datapool["override"]!="") { print $this->{$this->datapool["override"]}(); } else { ?'.'>',$_str);
		$_str=preg_replace('/<type\s+([a-zA-Z0-9_-]+)>/','<'.'?php if($this->type=="$1"){ ?'.'>',$_str);
		$_str=str_replace('</fragment>','<'.'?php } } ?'.'>',$_str);
		$_str=str_replace('</foreach>','<'.'?php } } ?'.'>',$_str);
		$_str=str_replace('</type>','<'.'?php } ?'.'>',$_str);	
		$_str=str_replace('</hidden>','<'.'?php } ?'.'>',$_str);
		$_str=str_replace('<hidden>','<'.'?php if(false){ ?'.'>',$_str);
		$_str=preg_replace('/\{{([a-zA-Z0-9_]+)\}}/','<'.'?php print $this->$1(); ?'.'>',$_str);
		$_str=preg_replace('/\{([a-zA-Z0-9_]+)\}/','<'.'?php print  $this->$1; ?'.'>',$_str);
		return  $_str;
	}
/* ================================================================================= */	
	function parsesubfragments($_text,&$_newfragmentist,$_parentfile)
	{
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
		
		if (count($_a1)==0) return;	
		if (count($_a1)!=count($_a2)) return;
		
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
		//	$_newfragmentist[trim(substr($_text,$res0,$res1-$res0-1))]=substr($_text,$res1,$res2-$res1);
			$this->whereIsSubFragments[trim(substr($_text,$res0,$res1-$res0-1))]=$_parentfile;
			$this->subFragments[$_parentfile][trim(substr($_text,$res0,$res1-$res0-1))]=$this->shablonize(substr($_text,$res1,$res2-$res1));
		}
		$res1=$_a1[0];
		$res2=$_a2[0];
		$res0=$res1+9;
		$res1=strpos($_text,'>',$res1)+1;
		//$_newfragmentist[trim(substr($_text,$res0,$res1-$res0-1))]=substr($_text,$res1,$res2-$res1);
		$this->whereIsSubFragments[trim(substr($_text,$res0,$res1-$res0-1))]=$_parentfile;
		$this->subFragments[$_parentfile][trim(substr($_text,$res0,$res1-$res0-1))]=$this->shablonize(substr($_text,$res1,$res2-$res1));
	}
/* ============================================================================== */

	//получение данных из .ini файла
	function loadAndParseIniFile($filename){
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
				$res=array_merge_recursive($res,array($subject=>$value));
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
