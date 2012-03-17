<?php

/*
	Класс - заготовка для вывода списка элементов, одного элемента, создания, редактирования элементов
*/
class CRUDController extends Controller
{
 

	/**
	* Список всех элементов
	*/	
 	function index()
	{
		$obj_list= $this->obj_name() . "_list";
		$model_name=$this->model_name();
		d()->{$obj_list} = d()->{$model_name};
	}
	
	function show($id)
	{
		 
		
		$model_name=$this->model_name();
		d()->this = d()->{$model_name}->find($id);
		if(count(d()->this)==0){
			d()->message="Объект не найден";
			return d()->error('404');
		}
		print d()->view();
		

	}

	function edit($id)
	{
		$obj_name= $this->obj_name();
		$model_name=$this->model_name();
		d()->this = d()->{$model_name}->find($id);
		 
		if(d()->validate($obj_name.'_update')){
			d()->this->title=d()->params['title'];
			d()->this->text=d()->params['text'];
			d()->this->save;
			header('Location: /'.$obj_name.'/');
			exit;
		}

	}

	function create()
	{
		$obj_name= $this->obj_name();
		$model_name=$this->model_name();
		if(d()->validate($obj_name.'_update')){
			d()->{$model_name}->create(array(
				'title'=>d()->params['title'],
				'text'=>d()->params['text']
			));
			header('Location: /'.$obj_name.'/');
			exit();
		}
		
		print d()->call($obj_name.'_edit_tpl');
	}

	
}


