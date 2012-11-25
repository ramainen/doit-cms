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
			return '/' . $this->get('url');
	}
}

