<?php

d()->route('/news/',function(){
	print 2+2;
	exit;
});

function main()
{
	//d()->content = d()->content();
	//print d()->render('main_tpl');
	d()->dispatch('content');
	
}

function hello_world()
{
	print "Hello, World!";
}