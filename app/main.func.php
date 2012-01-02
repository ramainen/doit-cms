<?php
function main()
{
	//var_dump(d()->Client);
//	$users=d()->Client->sql('select * from clients')->to_array;
	//var_dump($users);
	//print(d()->User->find(2)->clients[0]['title']);


	//var_dump(d()->Client->columns);
	d()->content = d()->content();
	print d()->render('main_tpl');
}
