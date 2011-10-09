Рецепты
=======
### Hello, World!
TODO: описать создание некой штуки, где есть шаблон, код, обращение к базе данных
	
Основной код (главная функция):

	function main()
	{
		//Запуск и получение внутреннего содержимого страницы (контентная часть)
		d()->content = d()->content(); 
		//Вывод переменной main_tpl или запуск функции main_tpl
		print d()->render('main_tpl');
	}

Либо если отличается что либо (пример - админка), этот код можно переопределить в роутере:

	/admin main admin

Основной код:
	
	function admin()
	{
		if(!isset($_SESSION['admin'])) {
			return d()->admin_authorisation();
		}
		d()->content = d()->content();
		return d()->render('admin_tpl');
	}

Далее, в шаблоне main.html или admin.html указывается `{{{content}}}`. Её PHP аналог:

	<?=d()->render('content') ?>

Таким образом, заранее определяется переменная content и затем выводится в нужном месте.
В общем случае можно заменить `{{{content}}}` на `{content}` или вообще на код:

	<?=d()->content ?>
	
Однако данная конструкция позволяет запускать функцию `content()` при отсуствии переменной `content`.
Также это позволяет переопределить внутри кода `content()` основной шаблон:

	if (d()->page->is_empty) {
		// Тут может быть внешний шаблон 404 страницы.
		d()->main_tpl=d()->error_404();
	}

### Непосредственный SQL

Иногда необходимо совершить сложный запрос посредством самого SQL.

	<ul>
	<foreach User->sql("select * from users where id < 4 order by `login`")->all as oneuser>
		<li>{oneuser.login}</li>
	</foreach>	
	</ul>
	
### Меню на сайте

Функция получения списка страниц (двухуровневое меню):

	function pages_list()
	{
		return d()->Text->where("text_id is NULL")->all;
	}

Шаблон:

	<foreach pages_list() as menu>
		<li>
			<a href="{menu.url}">{menu.title}</a>
			<ul>
			<foreach menu->texts as submenu>
				<li><a href="{submenu.url}">{submenu.title}</a></li>
			</foreach>	
			</ul>
		</li>
	</foreach>
	
Релизация того же самого при помощи tree экономит SQL-запросы, решая проблему N+1,
что актуально, если запросов большое количество. Более того, получение свойства во
множественном числе (`menu->texts`) — достаточно медленная операция.

Функция получения дерева:

	function pages_tree()
	{
		return d()->Text->tree;
	}

Шаблон:	
	
	<foreach pages_tree() as menu>
		<li>
			<a href="{menu.url}">{menu.title}</a>
			<ul>
			<foreach menu->tree as submenu>
				<li><a href="{submenu.url}">{submenu.title}</a></li>
			</foreach>	
			</ul>
		</li>
	</foreach>
	
