<?php

$show_controller_func = '

/**
* Отображение элемента
*/
function #table#_show($id)
{
	d()->this = d()->#model#->find($id);
	print d()->view();
}

';



$list_controller_func = '

	
/**
* Список всех элементов
*/	
function #table#_list($id)
{
	d()->#table#_list = d()->#model#;
	print d()->view();
}

';


$show_template = '

<h1>Просмотр: {.title} {{edit}}</h1>
{.text}

';


$list_template = '


<h1>Список: {{add \'#table#\'}}</h1>
<ul>
<foreach #table#_list>
<li><a href="/#table#/{.id}">{.title}</a> {{edit}} {{delete}}</li>
</foreach>
</ul>	 


';


$field_template = '[admin.fields]
small title "Заголовок"
rich text "Текст"
';

$router_template = '
/#table#/index		content    #table#_list
/#table#/			content    #table#_show
';
