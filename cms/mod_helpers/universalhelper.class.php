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
 */

/**
 * Универсальный класс для смягчения синтаксиса PHP
 * Умеет запускать функции вместо переменных, а также запрашивать многоязычные данные
 *
 */

class UniversalHelper
{
	protected $is_initialised=false;
	function try_to_init()
	{
		if(!$this->is_initialised){
			$this->init();
			$this->is_initialised=true;
		}
	}
	function __get($name)
	{
		$this->try_to_init();
		//Item.something
		if (method_exists($this, $name)) {
			return $this->{$name}();
		}

		//Item.ml_title
		if (substr($name, 0, 3) == 'ml_') {
			$lang = d()->lang;
			if ($lang != '') {
				return $this->{$lang.substr($name,2)};
			}
		}

		return $this->get($name);
	}
	function __call($name,$params)
	{
		$this->try_to_init();
		//Item.ml_title()
		if (substr($name, 0, 3) == 'ml_') {
			$lang = d()->lang;
			if ($lang != '') {
				return call_user_func_array(array($this,$lang.substr($name,2)),$params);
			}
		}

		return '';
	}
	function get($name)
	{
		return '';
	}
	function before()
	{
	
	}
	
	function after()
	{
	
	}
	function init()
	{
		
	}
}