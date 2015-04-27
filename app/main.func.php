<?php
 
 
d()->route('/news/:url/:url2',function($url,$url2){
	print "url- ";
	print $url;
	print $url2;
	exit;
});

d()->route('/news/:url+',function($url){
	print "url+ ";
	print $url;
	exit;
});



/*
приоритет задается количеством исчисляемых сивовлоа слеша
*/
d()->route('/news/:url*',function($url){
	print "url* ";
	print $url;
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