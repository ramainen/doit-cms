<?php

$scaffold_templates=array();


$scaffold_templates["controller_start"] = '

/**
* Контролер
*/
class #controller_name#Controller
{

';
$scaffold_templates["controller_end"] = '

}

';

$scaffold_templates["show_controller_func"] = '

/**
* Отображение элемента
*/
function #table#_show($id)
{
	d()->this = d()->#model#->find($id);
	print d()->view();
}

';



$scaffold_templates["list_controller_func"] = '

	
/**
* Список всех элементов
*/	
function #table#_index()
{
	d()->#table#_list = d()->#model#;
	print d()->view();
}

';

$scaffold_templates["show_controller_method"] = '

	/**
	* Отображение элемента
	*/
	function show($id)
	{
		d()->this = d()->#model#->find($id);
		print d()->view();
	}

';



$scaffold_templates["list_controller_method"] = '

	
	/**
	* Список всех элементов
	*/	
	function index()
	{
		d()->#table#_list = d()->#model#;
		print d()->view();
	}

';


$scaffold_templates["show_template"] = '

<h1>{.title} {{edit}}</h1>
{.text}

';


$scaffold_templates["list_template"] = '


<h1>Список: {{add \'#table#\'}}</h1>
<ul>
<foreach #table#_list>
<li><a href="/#table#/{.id}">{.title}</a> {{edit}} {{delete}}</li>
</foreach>
</ul>	 


';


$scaffold_templates["field_template"] = '[admin.fields]
small title "Заголовок"
rich text "Текст"
';

$scaffold_templates["router_template_func"] = '
/#table#/index		content    #table#_index
/#table#/			content    #table#_show
';

$scaffold_templates["router_template_oop"] = '
/#table#/index		content    #table##index
/#table#/			content    #table##show
';