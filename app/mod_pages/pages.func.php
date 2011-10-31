<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
function pages_show()
{
	d()->page = d()->Page->find(url(1));
	if (d()->page->is_empty) {
		print "Страница не существует".d()->add(array('pages','url'=>url(1)));
	} else {
		print d()->view();
	}

}

 
function pages_tree()
{
	return d()->Page->tree(d()->Page->index);
}
