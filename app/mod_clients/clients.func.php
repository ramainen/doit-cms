<?php
//контроллер
class ClientsController 
{
	function index()
	{
		d()->clients  = d()->Client->paginate(3);
		
		d()->paginator = d()->Paginator->generate(d()->clients  );

		d()->long_paginator = d()->Paginator->generate(30);
		print d()->view();
	}
	
	function show($id)
	{
		d()->client = d()->Client->find($id);
		print d()->view();
	}

	function edit($id)
	{
		d()->client = d()->Client->find($id);
		action('mailer');
		action('clients#update');
		
		print d()->view();
	}

	function create()
	{

		if(d()->validate('clients#update')){
			d()->Client->create(array(
				'title'=>d()->params['title'],
				'text'=>d()->params['text']
			));
			header('Location: /clients/');
		}

		print d()->clients_edit_tpl();
	}


	function update($params)
	{
		d()->client->title=$params['title'];
		d()->client->text=$params['text'];
		d()->client->save;
		header('Location: /clients/');
		exit;
	}
	function lucky($name='')
	{
		print "Счастливчик $name!";
	}
}

function mailer($params)
{
	print  "А вот тут отпарвка емейл апо адресу   и редирект";
	var_dump($params);
	exit();
}
