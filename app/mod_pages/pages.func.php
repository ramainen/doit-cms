<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
function pages_show()
{
	
	d()->page = d('Text')->find_by_url(url(1));
	 
	  
	if (d()->page->is_empty) {
		print "Страница не существует";
		d()->newurl=url(1);
		d()->table='texts';
		print d()->addbutton();
		d()->stop_next_chains();
		
	}

}
// возвращает двухуровневый массив страниц для меню
function pages_list()
{
	$retarray =  d()->Text->where("text_id = 0 or text_id is NULL")->all;
	return $retarray;
}

function pages_tree()
{
	return d()->Text->tree;
}
