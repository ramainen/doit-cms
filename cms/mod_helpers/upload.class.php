<?php
/*

	DoIt! CMS and VarVar framework
	Copyright (C) 2014 Fakhrutdinov Damir (aka Ainu)

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
 
*/

class Upload extends UniversalHelper
{
	public $upload_dir = 'storage';
	private $input_name='file';
	private $_new_name='';
	private $_exists = false;
	private $_md5=false;
	private $_extension = '';

	private $_allowed_files=array('jpg','jpeg','zip','png','doc','docx','xlsx','xls','gif','pdf');
	function __construct($input_name=false)
	{
		if($input_name===false)
		{
			if(isset($_FILES) && is_array($_FILES)){
				$x=array_keys($_FILES);
				if(isset($x[0])){
					$this->input_name = $x[0];
				}else{
					return $this;
				}
			}
		}else{
			$this->input_name = $input_name;
		}
		
		//Проверка - файл загружен или нет?
		if($_FILES[$this->input_name]['size']!=0){
			$this->_exists = true;
			$this->_extension = strtolower(end(explode(".", $_FILES[$this->input_name]['name'])));
		}
		
		
		
		if(!$this->_extension){
			$this->_exists = false;
		}
		
		if($this->_extension == 'php'){
			//Вот этого я вам загружать не дам. Хотите разрешить php - грузите "ручками".
			$this->_exists = false;
		}
		
		if(!in_array($this->_extension, $this->_allowed_files)){
			$this->_exists = false;
		}
		
		return $this;
	}
	
	/**
	* Описание функции
	*/
	function md5()
	{
		if($this->_md5==false && $this->exists()){
			$this->_md5 = md5_file($_FILES[$this->input_name]['tmp_name']);
		}
		return $this->_md5;
	}
	/**
	* Ограничивает разрешенные файлы
	*/
	function allow_files($param='jpg,jpeg,zip,png,doc,docx,xlsx,xls,gif,pdf')
	{
		$this->_allowed_files = array_map('trim',array_map('strtolower',explode(',',$param)));
		
		if(!in_array($this->_extension, $this->_allowed_files)){
			$this->_exists = false;
		}
		
		return $this;
	}
	/**
	* Ограничивает разрешенные файлы только картинками
	*/
	function allow_images()
	{
		return $this->allow_files('jpg,png,gif,jpeg');
	}

	/**
	* Ограничивает разрешенные файлы только картинками
	*/
	function only_images()
	{
		return $this->allow_files('jpg,png,gif,jpeg');
	}
	/**
	* Возвращает true, если всё номрально (файл приложен, он нужного типа и т.д.)
	*/
	function exists()
	{
		return $this->_exists;
	}
	
	/**
	* Перенос файла в папке storage (сохранение)
	*/
	function move($folder='',$filename=false)
	{
		if(!$this->_exists){
			return '';
		}
		if($folder==''){
			$path='/'.$this->upload_dir.'/';
		}else{
			$path='/'.$this->upload_dir.'/'.$folder.'/';
		}
		if($filename != false){
			//Внимание! Нехорошо в $filename передавать blabla.php.
			$file = $filename;
		}else{
			$file = $this->md5() . '.' . $this->extension();
		}
		if(!file_exists($_SERVER['DOCUMENT_ROOT'] . $path)){
			mkdir($_SERVER['DOCUMENT_ROOT'] . $path);
			chmod($_SERVER['DOCUMENT_ROOT'] . $path, 0777);
		}
		move_uploaded_file($this->tmp_name, $_SERVER['DOCUMENT_ROOT'] . $path . $file);
		chmod($_SERVER['DOCUMENT_ROOT'] . $path . $file, 0777);
		$this->_new_name=$path . $file;
		return $path . $file;
	}
	/**
	* Псевдоним функции move
	*/
	function save($folder='',$filename=false)
	{
		return $this->move($folder, $filename);
	}

	/**
	* Возвращает расширенеи файла
	*/
	function extension()
	{
		return $this->_extension;
	}
	
	function ext()
	{
		return $this->extension();
	}
	/**
	* Описание функции
	*/
	function new_name()
	{
		return $this->_new_name;
	}
	/**
	* Описание функции
	*/
	function name()
	{
		return $_FILES[$this->input_name]['name'];
	}
	
	/**
	* Описание функции
	*/
	function tmp_name()
	{
		return $_FILES[$this->input_name]['tmp_name'];
	}
	
}

