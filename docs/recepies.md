Рецепты
=======

### Меню на сайте

Функция получения списка страниц:

	function pages_list()
	{
		return d()->Text->where("text_id = 0 or text_id is NULL")->all;
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