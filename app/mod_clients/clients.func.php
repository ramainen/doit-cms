<?php
//контроллер
class clients_controller 
{
	function index()
	{
		d()->clients  = d()->Client->all;
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
		action('clients#update');
		action('mailer');
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
