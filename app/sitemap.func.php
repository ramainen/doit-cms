<?php
//Карта сайта
function show_sitemap()

{
	header ('Content-Type:application/xml');
	print d()->Sitemap->to_xml;
	exit();
}