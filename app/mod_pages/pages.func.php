<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
function pages_show()
{
	
	d()->page = d('Text')->find_by_url(url(1));
	 
	  
	if (d()->page->is_empty) {
		print "Страница не существует";
		print d()->add(array('texts','url'=>url(1)));
	} else {
		d()->route_to('/clients//edit');
		print d()->pages_show_tpl();
	}

}


function pages_two_columns()
{
	print "Два столбца";
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
