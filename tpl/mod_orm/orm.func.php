<?php

//Класс Active Record, обеспечивающий простую добычу данных
class ar
{
	public $options;
	function __construct($options=array()){
		//Опции по умолчанию и переданные
		$this->options=$options;
		//поле, по которому получаем данные. Для текттовых страниц это URL, для товаров это id, для пользователей это username или login и так далее.
		if(!isset($this->options['idfield'])) {
			$this->options['idfield']='id';
		}
		
		if(!isset($this->options['table'])) {
			if(doit()->table!='') {
				$this->options['table']=doit()->table;
			} else {
				$this->options['table']='data';
			}
		}
	}
	//получение данных из таблицы в виде массива - одной строки
	//указание связанных полей, которые надо получать в любом случае
	//получение т.н. свойств и списков
	//возможно использование в виде обхектов
	//возможно получение в виде обучного массива
	/*
	getRow должна уметь:
	1. поиск по id
	2. поиcк по ассоциативному массиву id=>значение
	getRows должна уметь:
	1. поиск по id родителя
	2. поиcк по ассоциативному массиву id=>значение
	*/
	public function getRow($id)
	{
		if($_line = mysql_fetch_array(mysql_query("select * from `".$this->options['table']."` where `".$this->options['idfield']."`='". mysql_real_escape_string ($id)."' limit 1"))) {
			return $_line;
		} else {
			return false;
		}
	}
}
