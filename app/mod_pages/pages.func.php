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

/**
 * Альтернативный контроллер для адресов вида /about/company/history
 * где about, company, history - три страницы с соотвествующими url, установленные в качестве дочерних
 * последовательно друг к другу
 *
 * @return mixed HTML-код
 */
function pages_chain_show()
{
	
	$params=func_get_args();
	$parent_id=0;
	print d()->view();
	foreach($params as $key=>$value){
		if($key==0){
			$curr_page=d()->Page->where('page_id is NULL');
		} else {
			$curr_page=d()->Page->where('page_id = ?',$parent_id);
		}
		$curr_page->where('`url` = ?',$value);
 
		if($curr_page->is_empty){
			print "Страница не существует".d()->add(array('pages','url'=>$value));
			return;
		}
		$parent_id = $curr_page->id;
	}
	d()->page = $curr_page;
	print d()->pages_show_tpl();
	
}
function pages_tree()
{
	return d()->Page->tree(d()->Page->index);
}

class Page extends ar
{
	function full_link()
	{
		$result_url=array();
		$curr_id=$this->id;
		$curr_parent=$this->page_id;
		$curr_url=$this->url;
		$antireqursy=100;
		$not_founded = true;
		while($not_founded && $antireqursy>0) {
			$antireqursy--;
			$result_url[]=$curr_url;
			if($curr_parent=='') {
				$not_founded = false;
				return implode('/',array_reverse( $result_url));
			}
			$parent_page=d()->Page->find($curr_parent);
			$curr_parent = $parent_page->page_id;
			$curr_url=$parent_page->url;
			$curr_id=$parent_page->id;
		}
	}
}