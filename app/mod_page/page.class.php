<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
class Page extends ActiveRecord
{
	function show_action($url)
	{
	 
		$url = url();
		d()->this = d()->Page->find_by_url($url);
		if (d()->this->is_empty) {
			d()->message="Страница не существует".d()->add(array('pages','url'=>$url));
			return d()->error('404');
		}
		 print d()->view();

	}



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

