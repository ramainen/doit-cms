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
 
 * d()->Paginator->generate(10,2);
 * d()->Paginator->generate(10);
 */
class Paginator extends UniversalHelper
{
	public $active='active';
	private $_is_bootstrap=false;
	public function setActive($active)
	{
		$this->active = $active;
		return $this;
	}
	public function bootstrap($is=true)
	{
		$this->_is_bootstrap=$is;
		return $this;
	}
	public function generate($allcount=1, $current=false){
		if(!is_numeric($allcount)){
			//Внезапно передали экземпля модели
			$model = $allcount;
			$allcount= ceil($model->found_rows/$model->per_page);
			$current = $model->current_page;
		}
		d()->paginator_left = '';
		d()->paginator_right = '';
		
		if($allcount<=1){
			return '';
		}
		if($current===false){
			if(isset($_GET['page'])){
				$current=(int)$_GET['page'];
			}else{
				$current=0;
			}
		}
		$all_url=$this->clearPagesInAdress($_SERVER['REQUEST_URI']);
		
		
		
		$result='';
		$allowed_pages=$this->getPagesArray($allcount,$current);
		$old_step=$allowed_pages[0]; //Виртуальный прошлый шаг
		
		if($this->_is_bootstrap){
			foreach ($allowed_pages as $i){
				$i++;
				 
				if (($i-$old_step)>1) {
					$result .= '<li class="disabled"><a href="#">...</a></li>';
				}
				
				$current_url=$this->drawPageInAdress($all_url,$i-1);
				$params=array('a',$i);
				$params['href']=$current_url;
				if($i-1==$current){
					
					$result .= '<li class="active">'.tag($params).'</li>';
				}else{
					$result .= '<li>'.tag($params).'</li>';
					
				}
				
			
				
				$old_step = $i;
			}
			$result = str_replace('&','&amp;',$result);
			return '<div class="pagination"><ul>'.$result.'</ul></div>';
		}else{
	
			foreach ($allowed_pages as $i){
				$i++;
				 
				if (($i-$old_step)>1) {
					$result .= ' <span class="paginator_dots">...</span> ';
				}
				
				$current_url=$this->drawPageInAdress($all_url,$i-1);
				$params=array('a',$i);
				$params['href']=$current_url;
				if($i-1==$current){
					$params['class']=$this->active;
				}
				$result .= tag($params);
			
				
				$old_step = $i;
			}
			
			if($current > 0){
				d()->paginator_left = $this->drawPageInAdress($all_url,$current-1);
			}
			
			if($current < $allowed_pages[count($allowed_pages)-1]){
				d()->paginator_right = $this->drawPageInAdress($all_url,$current+1);
			}
			$result = str_replace('&','&amp;',$result);
			return $result;
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
	
	public function getPagesArray($pages_count=1,$current=0)
	{
		if($pages_count<=0) {
			$pages_count=1;
		}
		
		if($current >= $pages_count) {
			$current = $pages_count-1;
		}
		
		$result=array();
		$interval=2; // Интервал - две страницы ДО текущей и две ПОСЛЕ
		//current от 0 и до конца
		//массив от 0 и до конца
		$start_page=$current-$interval;
		$end_page=$current+$interval;
		
		
		if ($start_page==-1) {
			//Проверка 1 [2] 3 4 5 ... 9 - соблюдение пяти цифр
			$end_page=$end_page+$interval-1;
		} elseif ($start_page<0) {
			//Проверка 1 2 3 [4] 5  6 ... 9 - соблюдение пяти цифр
			$end_page=$end_page+$interval;
		}
		
		if ($end_page-1 > $pages_count-1) {
			$start_page=$start_page-$interval;
		} elseif ($end_page > $pages_count-1) {
			$start_page=$start_page-$interval+1;
		}
		
		
		if ($start_page<0) {
			$start_page=0;
		}
		
		if ($end_page>$pages_count-1) {
			$end_page = $pages_count-1;
		}
		
		if($start_page>=3){
			$result[]=0;
		}
		
		if($start_page==2){
			$result[]=0;
			$result[]=1;
		}
		
		if($start_page==1){
			$result[]=0;
		}
		
		for ($i=$start_page;$i<=$end_page;$i++){
			$result[] = $i;
		}
		
		if ($end_page<=$pages_count-4) {
			$result[] =  $pages_count-1;
		}
		
		if($end_page==$pages_count-3){
			$result[] =  $pages_count-2;
			$result[] =  $pages_count-1;
		}
		
		if($end_page==$pages_count-2){
			$result[] =  $pages_count-1;
		}
		
		return $result;
		
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
