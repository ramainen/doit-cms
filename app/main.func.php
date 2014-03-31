<?php
function main()
{
	d()->content = d()->content();
	print d()->render('main_tpl');
}

function hello_world()
{
	print "Hello, World!";
}