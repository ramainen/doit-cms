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
	
	function test_multilangl_userdate()
	{
		d()->lang='ru';
		$this->assertEquals(d()->ml_userdate('11.11.2011'),'11 ноября 2011');
		$this->assertEquals(d()->ml_userdate('11.11.11'),'11 ноября 2011');
		d()->lang='en';
		$this->assertEquals(d()->ml_userdate('31.01.2011'),'January 31, 2011');
		$this->assertEquals(d()->ml_userdate('31.01.2011'),'January 31, 2011');
		
		
	}
	
	
	function test_paginator_class()
	{
		$this->assertCoverage('cms/mod_helpers/paginator.class.php');
		//Тестирование класса пагинатора
		$tmp=$_SERVER['REQUEST_URI'];
		$tmp2=$_GET;
		$paginator = d()->Paginator;
		
		$_SERVER['REQUEST_URI'] = '/news/';
		$this->assertEquals($paginator->generate(3,2),'<a href="/news/?page=0" >1</a><a href="/news/?page=1" >2</a><a href="/news/?page=2"  class="active" >3</a>');
		
		$paginator->setActive('active_link');
		$this->assertEquals($paginator->generate(3,2),'<a href="/news/?page=0" >1</a><a href="/news/?page=1" >2</a><a href="/news/?page=2"  class="active_link" >3</a>');
		
		$_GET['page']='1';
		
		//При одной или менее страниц их быть не должно вооюще
		$this->assertEquals($paginator->generate(1,2),'');
		$this->assertEquals($paginator->generate(0,2),'');
		$this->assertEquals($paginator->generate(1),'');
		
		
		$this->assertEquals($paginator->generate(3),'<a href="/news/?page=0" >1</a><a href="/news/?page=1"  class="active_link" >2</a><a href="/news/?page=2" >3</a>');

		$this->assertEquals($paginator->generate(3,0),'<a href="/news/?page=0"  class="active_link" >1</a><a href="/news/?page=1" >2</a><a href="/news/?page=2" >3</a>');
		$_GET=array();
		$this->assertEquals($paginator->generate(3),'<a href="/news/?page=0"  class="active_link" >1</a><a href="/news/?page=1" >2</a><a href="/news/?page=2" >3</a>');

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
		
	}
	
	
}
 
?>