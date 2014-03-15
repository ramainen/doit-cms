<?php
	
class HelpersTest extends Test
{
	public function test_coverage()
	{
		//$this->assertCoverage('cms/cms.php');
		$this->assertCoverage('cms/mod_helpers/helpers.func.php');
		/*$f = d()->form('sdasd');
		$f = d()->input(array());
		$f = d()->input(array('style'=>'color:red;'));
		$f = d()->input(array('class'=>'system'));*/
	}
	//Тест бесмысленный, яляется образцом =). Как и следующий.
	public function test_helperh()
	{
		$this->assertEquals('asd',d()->h('asd'));
		$this->assertNotEquals('ssd',d()->h('asd'));
	}
	
	function test_valid_email()
	{
		$this->assertTrue(d()->valid_email('user@mail.ru'));
		$this->assertTrue(d()->valid_email('__tony@mail.ru'));
		$this->assertFalse(d()->valid_email('@__tony@mail.ru'));
		$this->assertFalse(d()->valid_email('mail.ru'));
		$this->assertFalse(d()->valid_email('a@a.a'));
		$this->assertFalse(d()->valid_email('троглодит@почта.рф'));
		$this->assertTrue(d()->valid_email('a@a.ru'));
	}
	
	function test_declOfNum()
	{
		$this->assertEquals(d()->declOfNum(4,array('попугай','попугая','попугаев')),'попугая');
		$this->assertNotEquals(d()->declOfNum(4,array('попугай','попугая','попугаев')),'попугаев');
		$this->assertEquals(d()->declOfNum(11,array('попугай','попугая','попугаев')),'попугаев');
		$this->assertEquals(d()->declOfNum(111,array('попугай','попугая','попугаев')),'попугаев');
		$this->assertEquals(d()->declOfNum(0,array('попугай','попугая','попугаев')),'попугаев');
		$this->assertEquals(d()->declOfNum(81,array('попугай','попугая','попугаев')),'попугай');
		$this->assertEquals(d()->declOfNum('стопицот',array('попугай','попугая','попугаев')),'попугаев');
		
	}
	function test_preview()
	{
		//тестируем превью
		$this->assertEquals(d()->preview('/storage/1.gif'),'/storage/.thumbs/preview1_1.gif');
		$this->assertEquals(d()->preview(''),'');
		$this->assertEquals(d()->preview('/storage/'),'');
		$this->assertEquals(d()->preview('/storage/folder/2.gif'),'/storage/folder/.thumbs/preview1_2.gif');
		$this->assertEquals(d()->preview('/storage/folder/2.jpeg'),'/storage/folder/.thumbs/preview1_2.jpeg');
		$this->assertEquals(d()->preview('/storage/folder/2.GIF'),'/storage/folder/.thumbs/preview1_2.GIF');
		$this->assertEquals(d()->preview('нет картинки'),'');
		
		
	}
	
	function test_tag()
	{
		$this->assertEquals(d()->tag(array('div', '')),'<div></div>');
		$this->assertNotEquals(d()->tag(array('input')),'<input />');
		$this->assertEquals(d()->tag(array('input')),'<input>');
		$this->assertEquals(d()->tag(array('div', '123')),'<div>123</div>');
		$this->assertEquals(d()->tag(array('div', '123', 'color'=>'red')),'<div color="red" >123</div>');
		$this->assertEquals(d()->tag(array('div', 'color'=>'red')),'<div color="red" >');
		$this->assertEquals(d()->tag(array('div', '123', 'style'=>"color:red;")),'<div style="color:red;" >123</div>');
		$this->assertEquals(d()->tag(array('div', '123', 'class'=>"hidden blue")),'<div class="hidden blue" >123</div>');
	}
	
	function test_notice()
	{
		$this->assertEquals(d()->notice(),'');
		//Добавляем ошибку
		d()->add_notice('Errorka');
		$this->assertEquals(d()->notice(),'<ul style="padding:15px;padding-left:25px;border:1px solid red;" ><li>Errorka</li></ul>');
		//Хотим переопределять внешний вид ошибок
		$this->assertEquals(d()->notice(array('style'=>'color:black;')),'<ul style="color:black;" ><li>Errorka</li></ul>');
		$this->assertNonSpaceEquals(d()->notice(array('class'=>'red_error')),
			'<ul class="red_error"     style="padding:15px;padding-left:25px;border:1px solid red;" ><li>Errorka</li></ul>');
			
			$this->assertNonSpaceEquals(d()->notice(array('class'=>'red_error','style'=>'display:none;')),
			'<ul class="red_error"     style="display:none;" ><li>Errorka</li></ul>');
			
		d()->add_notice('SecondError');
		$this->assertEquals(d()->notice(array('style'=>'color:black;')),'<ul style="color:black;" ><li>Errorka</li><li>SecondError</li></ul>');
	}
	

	
	
	function test_paginator_class()
	{
		$this->assertCoverage('cms/mod_helpers/paginator.class.php');
		//Тестирование класса пагинатора
		$tmp=$_SERVER['REQUEST_URI'];
		$tmp2=$_GET;
		$paginator = d()->Paginator;
		
		$_SERVER['REQUEST_URI'] = '/news/';
		$this->assertEquals($paginator->generate(3,2),'<a href="/news/" >1</a><a href="/news/?page=1" >2</a><a href="/news/?page=2"  class="active" >3</a>');
		
		$paginator->setActive('active_link');
		$this->assertEquals($paginator->generate(3,2),'<a href="/news/" >1</a><a href="/news/?page=1" >2</a><a href="/news/?page=2"  class="active_link" >3</a>');
		
		$_GET['page']='1';
		
		//При одной или менее страниц их быть не должно вооюще
		$this->assertEquals($paginator->generate(1,2),'');
		$this->assertEquals($paginator->generate(0,2),'');
		$this->assertEquals($paginator->generate(1),'');
		
		
		$this->assertEquals($paginator->generate(3),'<a href="/news/" >1</a><a href="/news/?page=1"  class="active_link" >2</a><a href="/news/?page=2" >3</a>');

		$this->assertEquals($paginator->generate(3,0),'<a href="/news/"  class="active_link" >1</a><a href="/news/?page=1" >2</a><a href="/news/?page=2" >3</a>');
		$_GET=array();
		$this->assertEquals($paginator->generate(3),'<a href="/news/"  class="active_link" >1</a><a href="/news/?page=1" >2</a><a href="/news/?page=2" >3</a>');

		$this->assertEquals($paginator->clearPagesInAdress('/news/'),'/news/');
		$this->assertEquals($paginator->clearPagesInAdress('/news/?'),'/news/');
		$this->assertEquals($paginator->clearPagesInAdress('/news/?page=23'),'/news/');
		$this->assertEquals($paginator->clearPagesInAdress('/news/?user=ainu&page=23'),'/news/?user=ainu');
		$this->assertEquals($paginator->clearPagesInAdress('/news/?user=ainu&page=tarakan&user=123'),'/news/?user=ainu&user=123');
		$this->assertEquals($paginator->clearPagesInAdress('/news/?page=2&user=123'),'/news/?user=123');
		$this->assertEquals($paginator->clearPagesInAdress('/news/?page=2&user[]=123&user[]=123&&page=3'),'/news/?user[]=123&user[]=123');
		$this->assertEquals($paginator->clearPagesInAdress('/news/?2=2&&&page=2&user=123?page=2&page=2'),'/news/?2=2&&&user=123?page=2');
		$this->assertEquals($paginator->clearPagesInAdress('/news/?&&&&&&&page=2'),'/news/');
		$this->assertEquals($paginator->clearPagesInAdress('/?admin&page=2'),'/?admin');
		
		$this->assertEquals($paginator->drawPageInAdress('/news/',2),'/news/?page=2');
		$this->assertEquals($paginator->drawPageInAdress('/news/?here=1',3),'/news/?here=1&page=3');
		$this->assertEquals($paginator->drawPageInAdress('/news/?here=1&low',3),'/news/?here=1&low&page=3');
		$this->assertEquals($paginator->drawPageInAdress('/news/?',3),'/news/?page=3');

		
		//Самые обычные расклады - точки не появляются вообще
		$this->assertEquals($paginator->getPagesArray(1,0),array(0));
		$this->assertEquals($paginator->getPagesArray(5,0),array(0,1,2,3,4));
		$this->assertEquals($paginator->getPagesArray(5,1),array(0,1,2,3,4));
		$this->assertEquals($paginator->getPagesArray(5,2),array(0,1,2,3,4));
		$this->assertEquals($paginator->getPagesArray(5,3),array(0,1,2,3,4));
		$this->assertEquals($paginator->getPagesArray(5,4),array(0,1,2,3,4));
		$this->assertEquals($paginator->getPagesArray(6,4),array(0,1,2,3,4,5));
		$this->assertEquals($paginator->getPagesArray(6,2),array(0,1,2,3,4,5));
		$this->assertEquals($paginator->getPagesArray(6,0),array(0,1,2,3,4,5));
		
		
		//Исключение
		$this->assertEquals($paginator->getPagesArray(0,300),array(0));
		
		
		$this->assertEquals($paginator->getPagesArray(2,0),array(0,1));
		$this->assertEquals($paginator->getPagesArray(3,0),array(0,1,2));
		$this->assertEquals($paginator->getPagesArray(4,0),array(0,1,2,3));
		$this->assertEquals($paginator->getPagesArray(10,5),array(0,3,4,5,6,7,8,9));
		$this->assertEquals($paginator->getPagesArray(10,6),array(0,4,5,6,7,8,9));
		$this->assertEquals($paginator->getPagesArray(10,7),array(0,5,6,7,8,9));
		$this->assertEquals($paginator->getPagesArray(10,8),array(0,5,6,7,8,9));
		$this->assertEquals($paginator->getPagesArray(5,2),array(0,1,2,3,4));
		$this->assertEquals($paginator->getPagesArray(7,3),array(0,1,2,3,4,5,6));
		$this->assertEquals($paginator->getPagesArray(10,3),array(0,1,2,3,4,5,9));
		$this->assertEquals($paginator->getPagesArray(10,2),array(0,1,2,3,4,9));
		$this->assertEquals($paginator->getPagesArray(10,1),array(0,1,2,3,4,9));
		$this->assertEquals($paginator->getPagesArray(1000,5),array(0,3,4,5,6,7,999));
		$this->assertEquals($paginator->getPagesArray(1000,4),array(0,1,2,3,4,5,6,999));
		$this->assertEquals($paginator->getPagesArray(10,9),array(0,5,6,7,8,9));
		$this->assertEquals($paginator->getPagesArray(1000,0),array(0,1,2,3,4,999));
		
		
		
		//
		
		
		
		
		$_SERVER['REQUEST_URI'] = $tmp;
		$_GET=$tmp2;
	}

	function test_nice_input()
	{
		
		//$this->assertEquals(d()->prepare_smart_array(" 'asdasd' 'asdas'   'input' class = \"admin\" 'use' ='as asd asd asd'"),array('input','class'=>'admin'));
		$this->assertEquals(d()->prepare_smart_array(" 'input' "),array('input'));
		$this->assertEquals(d()->prepare_smart_array(" 'input' 'another' "),array('input','another'));
		$this->assertEquals(d()->prepare_smart_array(" 'input' 'select' = '23'"),array('input','select'=>'23'));
		
		$this->assertEquals(d()->prepare_smart_array(" \"input\" class=\"admin\""),array('input','class'=>'admin'));
		$this->assertEquals(d()->prepare_smart_array(" class = \"admin\" selected='false'"),array('class'=>'admin', 'selected'=>'false'));
		$this->assertEquals(d()->prepare_smart_array('  "srok"  class = "textinput type3 inp11 col3 row8"'),array('srok', 'class'=>'textinput type3 inp11 col3 row8'));
		
		
		
	}
	function test_multilangl_userdate()
	{
		$this->assertCoverage('cms/mod_helpers/date.class.php');
		d()->lang='tt';
		$this->assertEquals(d()->ml_userdate('01.01.11'),'1 гыйнвар 2011');
		
		d()->lang='ru';
		$this->assertEquals(d()->ml_userdate('11.11.2011'),'11 ноября 2011');
		$this->assertEquals(d()->ml_userdate('11.11.11'),'11 ноября 2011');
		d()->lang='en';
		$this->assertEquals(d()->ml_userdate('31.01.2011'),'January 31, 2011');
		$this->assertEquals(d()->ml_userdate('31.01.2011'),'January 31, 2011');
		
		
	}

	function test_date()
	{
		$this->assertCoverage('cms/mod_helpers/date.class.php');
		$this->assertEquals(d()->Date('14.03.2015')->ru_user(), '14 марта 2015');
		$this->assertEquals(d()->Date('14.01.2015')->ru_user(), '14 января 2015');
		$this->assertEquals(d()->Date('')->ru_user(), '');
		$this->assertEquals(d()->Date('2014-02-24 20:56:49')->ru_user(), '24 февраля 2014');
		$this->assertEquals(d()->Date('02-12-2024')->ru_user(), '2 декабря 2024');
		$this->assertEquals(d()->Date('02-12-2024')->to_simple(), '02.12.2024');
		$this->assertEquals(d()->Date('01-01-2024')->to_simple(), '01.01.2024');
		$this->assertEquals(d()->Date('0000-00-00 00:00:00')->to_simple(), '');

		$this->assertEquals(d()->Date('22.03.2014')->to_mysql(), '2014-03-22 12:00:00');
		$this->assertEquals(d()->Date('02-12-2014')->to_mysql(), '2014-12-02 12:00:00');
		$this->assertEquals(d()->Date('31-01-2014')->to_mysql(), '2014-01-31 12:00:00');
		$this->assertEquals(d()->Date('31.01.2014')->to_mysql(), '2014-01-31 12:00:00');
		$this->assertEquals(d()->Date('')->to_mysql(), '');

		$this->assertEquals(d()->Date('02-01-2024')->tt_user(), '2 гыйнвар 2024');
		$this->assertEquals(d()->Date('02-12-2024')->tt_user(), '2 декабрь 2024');
		
		$this->assertEquals(d()->Date('02-09-2024')->tt_user(), '2 сентябрь 2024');

		$this->assertEquals(d()->Date('02-09-2024')->ru_user_mini(), '2 сен 2024');
		$this->assertEquals(d()->Date('2024-09-02')->ru_user_mini(), '2 сен 2024');
		$this->assertEquals(d()->Date('2024-09-02')->ru_user(), '2 сентября 2024');


		$this->assertEquals(d()->Date('02.09.2024')->ru_user_mini(), '2 сен 2024');
		$this->assertEquals(d()->Date('')->ru_user_mini(), '');

		$this->assertEquals(d()->Date('02-01-2024')->tt_user_mini(), '2 гыйн 2024');
		
		$this->assertEquals(d()->Date('2.2.98')->tt_user(), '2 февраль 1998');
		$this->assertEquals(d()->Date('2.02.32')->ru_user(), '2 февраля 2032');
		$this->assertEquals(d()->Date('2.02 32')->ru_user(), '2 февраля 2032');

		$this->assertEquals(d()->Date('tomorrow')->to_simple(), date('d.m.Y', time() + 86400));

		$this->assertEquals(d()->Date('24 февраля 2014')->ru_user(), '24 февраля 2014');
		$this->assertEquals(d()->Date('1 гыйнвар 2014')->ru_user(), '1 января 2014');
		$this->assertEquals(d()->Date('01 гыйнвар 2014')->ru_user(), '1 января 2014');
		
		$this->assertEquals(d()->Date('01 02 03')->ru_user(), '1 февраля 2003');
		$this->assertEquals(d()->Date('01 02 1998')->ru_user(), '1 февраля 1998');
		$this->assertEquals(d()->Date('1 2 3')->ru_user(), '1 февраля 2003');
		$this->assertEquals(d()->Date('1 2 2003')->ru_user(), '1 февраля 2003');
		$this->assertEquals(d()->Date('2001 02 03')->ru_user(), '3 февраля 2001');

		$this->assertEquals(d()->Date('January 23, 2014')->ru_user(), '23 января 2014');
		$this->assertEquals(d()->Date('01 сентябрь 2014')->ru_user(), '1 сентября 2014');
		$this->assertEquals(d()->Date('01 сентябрь 2014')->tt_user(), '1 сентябрь 2014');

	}
}
 
?>