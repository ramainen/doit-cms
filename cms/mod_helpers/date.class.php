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
 * Класс для удобного использования дат и манипуляций с ними. Базовое использование d()->Date('25.03.2011')->to_russian
 *
 */
//TODO: таймштампы из mysql, RSS, время, минуты, секунды
class Date extends UniversalHelper
{
	private $date;
	public $month;
	public $ru_month;
	public $en_month;
	public $year;
	public $day;
	public $ru_months=array('','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
	public $en_months=array('','January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	function __construct($params=array(''))
	{

		if($params[0]!==false){
			$this->date = $params[0];
		}else{
			$this->date = time();
		}

		if(strpos($this->date,'.')===false){
			$this->date=Date('d.m.Y',$this->date);
		}
		$this->year=substr($this->date,6,4);
		$this->month=1*substr($this->date,3,2);
		
		
		if($this->year<99 && $this->year>50){
			$this->year=1*('19'.$this->year);
		}
		if($this->year<99 && $this->year<50){
			$this->year=1*('20'.$this->year);
		}
		$this->day=1*substr($this->date,0,2);
		$this->ru_month = $this->ru_months[$this->month];
		$this->en_month = $this->en_months[$this->month];
	}

	function to_russian()
	{
		return $this->ru_user();
	}
	function to_english()
	{
		return $this->en_user();
	}
	function ru_user()
	{
		return $this->day." ".$this->ru_month." ".$this->year;
	}
	function user()
	{
		return $this->ru_user();
	}
	function en_user()
	{
		return  $this->en_month." ".$this->day.", ".$this->year;
	}
	function ago()
	{
		return $this->ru_ago();
	}

	//Предупреждение: не оттестировано, пока не работает
	function ru_ago()
	{

	}
}