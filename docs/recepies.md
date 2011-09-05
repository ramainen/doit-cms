Рецепты
=======
### Hello, World!
TODO: описать создание некой штуки, где есть шаблон, код, обращение к базе данных

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
	
Релизация того же самого при помощи tree экономит SQL-запросы, решая проблему N+1, что актуально, если запросов большое количество. Более того, получение свойства во множественном числе (`menu->texts`) — достаточно медленная операция.

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
	
	
### Простой дамп переменной в шаблоне

Благодаря принципу работы хелперов, можно использовать следующую конструкцию:
	
	<pre>{testarrays|var_dump}</pre>
	
Так как хелпер - функция, принимающая в качестве параметра переменную, то данный сниппет преобразуется в 
	
	<pre><php print d()->var_dump(d()->testarrays); ?></pre>
	
`d()->var_dump()` Находит функцию var_dump и запускает её, передавая параметры.

