<?php

class ClientsController 
{
	function index()
	{
		d()->clients  = d()->Client->paginate(3);
		
		d()->paginator = d()->Paginator->generate(d()->clients);

		d()->long_paginator = d()->Paginator->generate(30);
		print d()->view();
	}
	
	function show($id)
	{
		d()->this = d()->Client->find($id);
		if(count(d()->this)==0){
			d()->message="Клиент не найден";
			return d()->error('404');
		}
		print d()->view();
	}

	function edit($id)
	{
		d()->client = d()->Client->find($id);
		
		if(d()->validate('clients_update')){
			d()->client->title=d()->params['title'];
			d()->client->text=d()->params['text'];
			d()->client->save;
			header('Location: /clients/');
			exit;
		}
		print d()->view();
	}

	function create()
	{

		if(d()->validate('clients_update')){
			d()->Client->create(array(
				'title'=>d()->params['title'],
				'text'=>d()->params['text']
			));
			header('Location: /clients/');
		}

		print d()->clients_edit_tpl();
	}

 
	function lucky($name='')
	{
		print "Счастливчик $name!";
	}
}
