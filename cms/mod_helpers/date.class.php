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
	public $stamp=false;
	public $ru_month_mini;
	public $en_month;
	public $ru_month_simple;
	public $en_month_simple;
	public $year;
	public $day;
	public $ru_months=array('','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
	public $ru_months_mini=array('','янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек');
	
	public $en_months=array('','January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	
	public $tt_months=array('','гыйнвар','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь');
	public $tt_months_mini=array('','гыйн','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек');
	
	public $ru_months_simple=array('','январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь');
	public $en_months_simple=array('','January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	
	function __construct($params=array(false))
	{

		if($params[0]!==false){
			$this->date = $params[0];
		}else{
			$this->date = 'today';
		}
		if(is_null($this->date)){
			$this->date='';
		}
		if(is_numeric($this->date) && $this->date > 0 ){
			if($this->date > 30000000){
				$this->date=date("Y-m-d H:i:s",$this->date);
			}else{
				$this->date=date("Y-m-d H:i:s",strtotime($this->date));
			}
		}

		if(is_object ($this->date) && $this->date instanceof MongoDate){
			$this->date = $this->date->sec;
		}
		
		$regular_expression_matched=false;
		$matches=array();
		//Попытка разбить дату на три составляющие
		if(preg_match('#(\d{1,4})[\s\.-](\d\d?)[\s\.-](\d{1,4})#', $this->date,$matches)){
			/*var_dump($matches);
			br();*/
			if(count($matches)==4){
				if($matches[1]>100){
					
					$year = $matches[1];
					


					$this->year =$year ; 
					
					$this->month = 1*$matches[2];
					$this->day = 1*$matches[3];
					$regular_expression_matched=true;					
				}else{
					$year =$matches[3];
					if($year<=99 && $year>60){
						$year=(1900 + $year);
					}
					if($year<=99 && $year<61){
						$year=(2000 + $year);
					}

					$this->year =$year ;
					
					if (1*$matches[2] < 13 ){
						$this->month = 1*$matches[2];
						$this->day = 1*$matches[1];
					}else{
						$this->month = 1*$matches[1];
						$this->day = 1*$matches[2];
					}

					$regular_expression_matched=true;					
				}
			
			}

		}
		// 25 января 04
		if(!$regular_expression_matched){
			if(preg_match('#([a-zA-Zа-яё0-9]+)[\s\.,-]+([a-zA-Zа-яё0-9]+)[\s\.\-,]+([a-zA-Zа-яё0-9]+)#ui', $this->date,$matches)){
				
				if(count($matches)==4){
					//Если вторая запись это месяц
					if(preg_match('#[a-zA-Zа-яА-Я]+#ui',$matches[2]) && $this->str_to_month($matches[2])!=0){
						$year = $matches[3];
						
						if($year<=99 && $year>60){
							$year=(1900 + $year);
						}
						if($year<=99 && $year<61){
							$year=(2000 + $year);
						}

						$this->year =$year ; 
						
						$this->month = 1*$this->str_to_month($matches[2]);
						$this->day = 1*$matches[1];
						$regular_expression_matched=true;
					}
					//Если первая часть это месяц
					elseif(preg_match('#[a-zA-Zа-яА-Я]+#ui',$matches[1]) && $this->str_to_month($matches[1])!=0){
						
						$year = $matches[3];
						
						if($year<=99 && $year>60){
							$year=(1900 + $year);
						}
						if($year<=99 && $year<61){
							$year=(2000 + $year);
						}

						$this->year =$year ; 
						
						$this->month = 1*$this->str_to_month($matches[1]);
						$this->day = 1*$matches[2];
						$regular_expression_matched=true;
					}
				}
			}
		}

		if($regular_expression_matched){
			//Всё хорошо
		}elseif($this->date!==''){
			$this->year = date('Y',strtotime($this->date));
			$this->month = (int)date('m',strtotime($this->date));
			$this->day = (int)date('d',strtotime($this->date));
		}
		$time_matches = array();
		$h=12;
		$m=0;
		$s=0;
		if(preg_match('#.*?\s(\d\d?):(\d\d?):(\d\d?)$#',$this->date,$time_matches)){
			$h=$time_matches[1];
			$m=$time_matches[2];
			$s=$time_matches[3];
		}
		if(preg_match('#.*?\s(\d\d?):(\d\d?)$#',$this->date,$time_matches)){
			$h=$time_matches[1];
			$m=$time_matches[2];
			$s=0;
		}		
		if($this->date!='' && $this->date!='0000-00-00 00:00:00'){
			$this->stamp = mktime ($h,$m,$s, $this->month, $this->day, $this->year);
		}


		$this->ru_month = $this->ru_months[$this->month];
		
		$this->ru_month_mini = $this->ru_months_mini[$this->month];
		$this->en_month = $this->en_months[$this->month];
		$this->ru_month_simple = $this->ru_months_simple[$this->month];
		$this->en_month_simple = $this->en_months_simple[$this->month];
		
		$this->tt_month = $this->tt_months[$this->month];
		$this->tt_month_mini = $this->tt_months_mini[$this->month];

		
	}
	function to_simple()
	{
		if($this->stamp == false){
			return '';
		}
		return date('d.m.Y',$this->stamp);
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
		if($this->ru_month == ''){
			return '';
		}
		return $this->day." ".$this->ru_month." ".$this->year;
	}

	function tt_user()
	{
		if($this->tt_month == ''){
			return '';
		}
		return $this->day." ".$this->tt_month." ".$this->year;
	}

	function ru_user_mini()
	{
		if($this->ru_month == ''){
			return '';
		}
		return $this->day." ".$this->ru_month_mini." ".$this->year;
	}
	function tt_user_mini()
	{
		if($this->tt_month == ''){
			return '';
		}
		return $this->day." ".$this->tt_month_mini." ".$this->year;
	}
	function user()
	{
		return $this->ru_user();
	}
	function user_mini()
	{
		return $this->ru_user_mini();
	}
	function en_user()
	{
		if($this->en_month == ''){
			return '';
		}
		return  $this->en_month." ".$this->day.", ".$this->year;
	}
	function to_mysql()
	{
		if($this->stamp==false){
			return '';
		}
		return date("Y-m-d H:i:s",$this->stamp);
	}
	function str_to_month($str)
	{
		if(in_array($str, $this->ru_months)){

			return array_search($str,$this->ru_months);
		}
		if(in_array($str, $this->tt_months)){

			return array_search($str,$this->tt_months);
		}
		if(in_array($str, $this->en_months)){

			return array_search($str,$this->en_months);
		}
		if(in_array($str, $this->ru_months_mini)){

			return array_search($str,$this->ru_months_mini);
		}
		
		if(in_array($str, $this->tt_months_mini)){

			return array_search($str,$this->tt_months_mini);
		}
		
		if(in_array($str, $this->ru_months_simple)){

			return array_search($str,$this->ru_months_simple);
		}

		

		return 0;
	}
	function ago($to=false)
	{
		if($to===false){
			$to=time();
		}
		return $this->ru_ago($to);
	}
	function when($to=false)
	{
		if($to===false){
			$to=time();
		}
		return $this->ru_when($to);
	}
	//Предупреждение: не оттестировано, пока не работает
	function ru_ago($to=false)
	{
		if($to===false){
			$to=time();
		}
		$timediff = $to - mktime(23,00,00,(int)$this->month, (int)$this->day, (int)$this->year);    
		$timediff = intval($timediff); 
		if($timediff < 60)   
		  $time = "$timediff секунд назад";  
		else if(($timediff = intval($timediff/60)) < 60)   
		  $time = "$timediff минут назад";  
		else if(($timediff = intval($timediff/60)) < 24)   
		   $time = "$timediff часов назад";  
		else if(($timediff = intval($timediff/24)) < 14)   
		   $time = "$timediff дней назад";  
		else if(($weeks= intval($timediff/7)) < 4)   
		  $time = "$weeks недели назад";  
		else if(($months= intval($timediff/30.4)) )   
		   $time = "$months ". declOfNum($months,array('месяц','месяца','месяцев')). " назад";  
		return $time; 
 
	}
	
	function ru_when($to=false)
	{

		if($to===false){
			$to=time();
		}

		$timediff = $to - mktime(23,00,00,$this->month, $this->day, $this->year);    
		$timediff  = $timediff/(60 *60 );
		
		if($timediff  < -48) {
		   $time = $this->ru_user();  
		} else if($timediff  < -24) {
		   $time = "Завтра";  
		} else if($timediff  < 0) {
		   $time = "Сегодня";  
		} else if($timediff  < 24){   
		   $time = "Вчера";   
		} else {
		   $time = $this->ru_user(); 
		}
		return $time; 
 
	}
	
}
