<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
class Page extends ActiveRecord
{
	function link()
	{
		if($this->get('link')!=''){
			return $this->get('link');
		}
		if($this->get('url')=='index'){
			return '/';
		}
			return '/' . $this->get('url');
	}
	
	function menu_title()
	{
		if($this->get('menu_title')!=''){
			return $this->get('menu_title');
		}
			return $this->get('title');
	}
}

