Примеры запросов Active Record
==============================

Поиск пользователя - автора страницы:

	d()->Page->find(33)->user;

Поиск страницы комментария и упрощённый для понимания аналог:

	d()->Comment(23)->page;

	//Аналог
	$comment = d()->Comment->find(23);
	$current_page_id = $comment->page_id;
	$page=d()->Page->find($current_page_id);

	//Получаем данные
	$page->title;

Список комментариев на странице с ID 45 и упрощённый для понимания аналог:

	d()->Page->find(45)->comments;

	//Аналог
	$page=d()->Page->find(45);
	$page_id = $page->id;
	$comments=d()->Comment->where('page_id = ?',$page_id);


Поиск дочерних объектов из текущей страницы:

	d()->Page->find(45)->pages;
	//SELECT * FROM `pages` where `page_id` = 45


	d()->Page->find(45)->comments;
	//SELECT * from comments where `page_id` = 45


	d()->User->find(23)->pages
	select * from pages where user_id=23

	d()->Page->find_by_url('index');
	d()->Page->find_by_title('Зывыфв');

	d()->Page->find_by('url','Зывыфв');

	d()->Page->where("title = '".e($title)."' or text='".e($text).'");

	d()->Page->where("title = ? or text= ? ", $title, $text);


Постраничная навигация (если пришёл GET[post]):

	$obj= d()->Page->where("title = ? or text= ? ", $title, $text);
	if(!empty($_GET['page']){
	    $obj->limit($_GET['page']*10.", 10")
	}


Установка глобальной переменной.

	d()->zagolovok = 'sadasd'

ООП-подход:

	$obj = d()->User;
	$obj = new Page();

Хранение таблицы в переменной:

	$table='User';
	d($table)->find(45);

Образцы имён моделей:

	d()->Catalog
	d()->News
	d()->Option