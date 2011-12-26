<?php
function main()
{
	$arr=d()->Client;
	

	d()->content = d()->content();
	print d()->render('main_tpl');
}
