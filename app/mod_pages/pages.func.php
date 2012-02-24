<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
function pages_show()
{
	d()->page = d()->Page->find(url(1));
	if (d()->page->is_empty) {
	
		//d()->prepare_content('content',"Страница не существует".d()->add(array('pages','url'=>url(1))));
		d()->message="Страница не существует".d()->add(array('pages','url'=>url(1)));
		return d()->error('404');
		
	} else {
		print d()->view();
	}

}
