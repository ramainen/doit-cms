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
 * Класс для генерации постраничной навигации
 *
 */
class Paginator extends UniversalHelper
{
	public function generate($allcount, $current=false){
		if($current===false){
			if(isset($_POST['page'])){
				$current=(int)$_POST['page'];
			}else{
				$current=0;
			}
		}
		$current_url=$this->clearPagesInAdress($_SERVER['REQUEST_URI']);
		
		$result='';
		for($i=1;$i<=$allcount;$i++){
			$result .= '<a href=""></a>';
		}
		
	}
	
	public function drawPageInAdress($url,$value){
		if(strpos($url,'?')===false){
			return $url.'?page='.$value;
		}else{
			if(strpos($url,'?')==strlen($url)-1){
				return $url.'page='.$value;	
			}
			return $url.'&page='.$value;
		}
	}
	public function clearPagesInAdress($adress){
		
		$first_quest=strpos($adress,'?');
		if($first_quest===false){
			return $adress;
		}
		
		$first_part=substr($adress,0,$first_quest);
		
		$adress=substr($adress,$first_quest+1);
		//Странный код, согласен, но он проходит тесты, собенно последние
		$adress=preg_replace('/\&page=.*?&/','&',$adress);
		$adress=preg_replace('/\&page=.*$/','&',$adress);
		$adress=preg_replace('/^page=.*?&/','&',$adress);
		$adress=preg_replace('/^page=.*$/','',$adress);
		$adress=preg_replace('/^&+/','',$adress);
		$adress=preg_replace('/&+$/','',$adress);
		
		if($adress !=''){
			$adress='?'.$adress;	
		}
		
		return $first_part.$adress;
		
	}
	
}