<?php

/*
	Класс - заготовка для вывода списка элементов и одного элемента
*/
class SimpleController extends Controller
{
	
	/**
	* Отображение элемента
	*/
	function show($id)
	{
		$model_name=$this->model_name();
		d()->this = d()->{$model_name}->find($id);
		print d()->view();
	}
	

	/**
	* Список всех элементов
	*/	
	function index()
	{
		$obj_list= $this->obj_name() . "_list";
		$model_name=$this->model_name();
		d()->{$obj_list} = d()->{$model_name};
		print d()->view();
	}


	
}


