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
	
	function test_transliterate_url()
	{
		
		
		$this->assertEquals('dveri-okna-skobyanye-izdeliya',d()->transliterate_url('Двери, окна, скобяные изделия')); 
		$this->assertEquals('komnata',d()->transliterate_url('Комната')); 
		$this->assertEquals('komnata-komnat',d()->transliterate_url('Комната-комнат')); 
		$this->assertEquals('komnata-komnat',d()->transliterate_url('Комната комнат')); 
		$this->assertEquals('komnata-komnat',d()->transliterate_url('Комната, комнат')); 
		$this->assertEquals('komnata-komnat',d()->transliterate_url(',Комната, комнат,')); 
		$this->assertEquals('komnata-komnat',d()->transliterate_url('Комната, , , - , ,комнат')); 
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
	function test_super_declOfNum()
	{
		
		$this->assertEquals(d()->declOfNum(4,'попугай','попугая','попугаев'),'попугая');
		$this->assertEquals(d()->declOfNum(1,'комментарий','комментария','комментариев'),'комментарий');
		$this->assertEquals(d()->declOfNum(2,'комментарий','комментария','комментариев'),'комментария');

		$this->assertEquals(declOfNum(2,'новость'),'новости');
		$this->assertEquals(declOfNum(100,'новость'),'новостей');
		$this->assertEquals(declOfNum(1,'комментарий'),'комментарий');

		$this->assertEquals(declOfNum(21,'комментарий'),'комментарий');
		$this->assertEquals(declOfNum(222,'комментарий'),'комментария');
		$this->assertEquals(declOfNum(100,'комментарий'),'комментариев');
		$this->assertEquals(declOfNum(100,'месяц'),'месяцев');
		$this->assertEquals(declOfNum(2,'месяц'),'месяца');
		$this->assertEquals(declOfNum(100,'магия'),'магий');
		$this->assertEquals(declOfNum(2,'магия'),'магии');
		$this->assertEquals(declOfNum(100,'секунда'),'секунд');
		$this->assertEquals(declOfNum(22,'человек'),'человека');
		$this->assertEquals(declOfNum(22,'человек'),'человека');
		$this->assertEquals(declOfNum(22,'год'),'года');
		$this->assertEquals(declOfNum(22,'ножницы'),'ножниц');
		$this->assertEquals(declOfNum(1,'ножницы'),'ножницы');
		$this->assertEquals(declOfNum(100,'ножницы'),'ножниц');
		$this->assertEquals(declOfNum(100,'очки'),'очков');
		$this->assertEquals(declOfNum(100,'очко'),'очков');
		$this->assertEquals(declOfNum(2,'очко'),'очка');
		$this->assertEquals(declOfNum(100,'пользователь'),'пользователей');
		$this->assertEquals(declOfNum(100,'запись'),'записей');
		$this->assertEquals(declOfNum(1002,'запись'),'записи');
		$this->assertEquals(declOfNum(1002,'контрагент'),'контрагента');
		$this->assertEquals(declOfNum(100,'контрагент'),'контрагентов');

		$this->assertEquals(declOfNum(2,'индеец'),'индейца');
		$this->assertEquals(declOfNum(100,'товар'),'товаров');
		$this->assertEquals(declOfNum(2,'гонец'),'гонца');
		$this->assertEquals(declOfNum(100,'гонец'),'гонцов');
		$this->assertEquals(declOfNum(100,'товар'),'товаров');
		$this->assertEquals(declOfNum(2,'товар'),'товара');
		$this->assertEquals(declOfNum(2,'ноябрь'),'ноября');
		$this->assertEquals(declOfNum(200,'ноябрь'),'ноябрей');
		$this->assertEquals(declOfNum(2,'имперец'),'имперца');
		$this->assertEquals(declOfNum(200,'имперец'),'имперцев');
		$this->assertEquals(declOfNum(2,'норвежец'),'норвежца');
		$this->assertEquals(declOfNum(200,'норвежец'),'норвежцев');
	
		$this->assertEquals(declOfNum(2,'слово'),'слова');
		$this->assertEquals(declOfNum(200,'слово'),'слов');
	
		$this->assertEquals(declOfNum(2,'потеря'),'потери');
		$this->assertEquals(declOfNum(200,'потеря'),'потерь');
	
	
		$this->assertEquals(declOfNum(2,'мистика'),'мистики');
		$this->assertEquals(declOfNum(200,'мистика'),'мистик');
	
		$this->assertEquals(declOfNum(2,'человек'),'человека');
		$this->assertEquals(declOfNum(200,'человек'),'человек');
	
		$this->assertEquals(declOfNum(2,'дело'),'дела');
		$this->assertEquals(declOfNum(200,'дело'),'дел');
	
		$this->assertEquals(declOfNum(2,'жизнь'),'жизни');
		$this->assertEquals(declOfNum(200,'жизнь'),'жизней');
	
		$this->assertEquals(declOfNum(2,'день'),'дня');
		$this->assertEquals(declOfNum(200,'день'),'дней');
	
		$this->assertEquals(declOfNum(2,'раз'),'раза');
		$this->assertEquals(declOfNum(200,'раз'),'раз');
	

		$this->assertEquals(declOfNum(2,'кусочек'),'кусочка');
		$this->assertEquals(declOfNum(200,'кусочек'),'кусочков');
	

		$this->assertEquals(declOfNum(2,'пискля'),'пискли');
		$this->assertEquals(declOfNum(200,'пискля'),'писклей');
	


		$this->assertEquals(declOfNum(2,'конёк'),'конька');
		$this->assertEquals(declOfNum(200,'конёк'),'коньков');
	
 
		$this->assertEquals(declOfNum(2,'мгновение'),'мгновения');
		$this->assertEquals(declOfNum(200,'мгновение'),'мгновений');
	
 
		$this->assertEquals(declOfNum(2,'неделя'),'недели');
		$this->assertEquals(declOfNum(200,'неделя'),'недель');
	
 
		$this->assertEquals(declOfNum(2,'век'),'века');
		$this->assertEquals(declOfNum(200,'век'),'веков');
	
		$this->assertEquals(declOfNum(2,'индеец'),'индейца');
		$this->assertEquals(declOfNum(200,'индеец'),'индейцев');
	
		$this->assertEquals(declOfNum(2,'китаец'),'китайца');
		$this->assertEquals(declOfNum(200,'китаец'),'китайцев');
	
		$this->assertEquals(declOfNum(2,'американец'),'американца');
		$this->assertEquals(declOfNum(200,'американец'),'американцев');
	
		$this->assertEquals(declOfNum(2,'африканец'),'африканца');
		$this->assertEquals(declOfNum(200,'африканец'),'африканцев');
	
		$this->assertEquals(declOfNum(2,'боец'),'бойца');
		$this->assertEquals(declOfNum(200,'боец'),'бойцов');
	
		$this->assertEquals(declOfNum(2,'молодец'),'молодца');
		$this->assertEquals(declOfNum(200,'молодец'),'молодцов');
	 
		$this->assertEquals(declOfNum(2,'гвардеец'),'гвардейца');
		$this->assertEquals(declOfNum(200,'гвардеец'),'гвардейцев');
	   
		$this->assertEquals(declOfNum(2,'печенька'),'печеньки');

		$this->assertEquals(declOfNum(200,'печенька'),'печенек');


		$this->assertEquals(declOfNum(2,'чюченька'),'чюченьки');
		$this->assertEquals(declOfNum(200,'чюченька'),'чюченек');
	 
		$this->assertEquals(declOfNum(2,'буча'),'бучи');
		$this->assertEquals(declOfNum(200,'буча'),'буч');
	 
		$this->assertEquals(declOfNum(2,'жуга'),'жуги');
		$this->assertEquals(declOfNum(200,'жуга'),'жуг');
	   
		$this->assertEquals(declOfNum(2,'жужа'),'жужи');
		$this->assertEquals(declOfNum(200,'жужа'),'жуж');
	 
		$this->assertEquals(declOfNum(2,'душа'),'души');
		$this->assertEquals(declOfNum(200,'душа'),'душ');
	 
		$this->assertEquals(declOfNum(2,'шуша'),'шуши');
		$this->assertEquals(declOfNum(200,'шуша'),'шуш');
	 
   
		$this->assertEquals(declOfNum(2,'дурь'),'дури');
		$this->assertEquals(declOfNum(200,'дурь'),'дурей');

		$this->assertEquals(declOfNum(2,'кефаль'),'кефали');
		$this->assertEquals(declOfNum(200,'кефаль'),'кефалей');
	 
		$this->assertEquals(declOfNum(2,'штаны'),'штанов');
		$this->assertEquals(declOfNum(200,'штаны'),'штанов');
	 
 

		$this->assertEquals(declOfNum(200,'макака'),'макак');
		
		$this->assertEquals(declOfNum(22,'child'),'children');
		$this->assertEquals(declOfNum(1,'child'),'child');


		//Админка
		$this->assertEquals(d()->declOfNum(array(2,'комментарий')),'комментария');		
/*
		$lang = d()->lang;
		
		d()->lang='tt';
		$this->assertEquals(d()->declOfNum(22,'язык'),'языклар');
		$this->assertEquals(d()->declOfNum(22,'хайван'),'хайваннар');
		$this->assertEquals(d()->declOfNum(1,'хайван'),'хайван');
		


		d()->lang=$lang;
*/		
		
	}


	function test_online_super_declOfNum()
	{
		 
		//Проверка 2 вида (родительный падеж)
		$words = array('беззаботность',"туркмен","юность","ручка","кусок"); //проверено
		$words = array('год' ,'дело',"жизнь",'день','рука','раз');//проверено
		$words = array('кусочек' ,'пискля',"жучара",'пожарник','документ','отчёт');//проверено
		$words = array('алмагель' , 'протокол', 'смартфон','страница','крыса','конёк');//проверено
		$words = array('секунда',"минута","год","час","век","мгновение","день","неделя");//проверено
		$words = array("индеец","китаец","индус","американец","африканец","боец","молодец");//проверено
		$words = array("женщина","сторона","вопрос","возможность","результат","группа","действие");//проверено
		$words = array("блюм","чюченька","буча","кушон","трюль","жуга","дыгун");//проверено
		$words = array("мука","жужа","буча","дура","душа","дума","шуша");//проверено
		$words = array("портфель","емеля","звонарь","январь","дурь","тюль","кефаль");//проверено
		$words = array();
		foreach ($words as $word){
			$xml = file_get_contents('http://api.morpher.ru/WebService.asmx/GetXml?s='.$word);

			$p = xml_parser_create();
			xml_parse_into_struct($p, $xml, $vals, $index);
			xml_parser_free($p);
			 
			 
			;
			$rodit = $vals[1]['value'];
			$mn_rodit = $vals[14]['value'];
			$this->assertEquals(declOfNum(2, $word),$rodit);
			$this->assertEquals(declOfNum(100, $word),$mn_rodit);

		}
 
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
		$this->assertEquals(d()->Date('today')->to_simple(), date('d.m.Y'));
		$this->assertEquals(d()->Date('today')->to_simple(), d()->Date->to_simple());

		//назваия месяецев
		$this->assertEquals(d()->Date->str_to_month('ноября'), 11);
		$this->assertEquals(d()->Date->str_to_month('февраля'), 2);
		$this->assertEquals(d()->Date->str_to_month('гыйнвар'), 1);



		$this->assertEquals(d()->Date('24 февраля 2014')->ru_user(), '24 февраля 2014');
		$this->assertEquals(d()->Date('2014-03-16 12:00:00')->ru_user(), '16 марта 2014');
		$this->assertEquals(d()->Date('24 янв 2014')->ru_user(), '24 января 2014');
		
		$this->assertEquals(d()->Date('24 янв 2014')->user(), '24 января 2014');
		$this->assertEquals(d()->Date('24 янв 2014')->user_mini(), '24 янв 2014');
		$this->assertEquals(d()->Date('24 янв 2014')->tt_user_mini(), '24 гыйн 2014');

		$this->assertEquals(d()->Date('16 марта 2014')->stamp, 1394956800);
		$this->assertEquals(d()->Date(1394956800)->ru_user(), '16 марта 2014');
		
		//Мы не можем вставлять сегодняшнюю дату - тест отвалится завтра
		$this->assertEquals(d()->Date('24 янв 2014')->ago(d()->Date('16 марта 2014')->stamp), '1 месяц назад');
		$this->assertEquals(d()->Date('15 марта 2013')->ru_ago(d()->Date('16 марта 2014')->stamp), '12 месяцев назад');
		$this->assertEquals(d()->Date('15 марта 2013')->ago(d()->Date('16 марта 2014')->stamp), '12 месяцев назад');
		$this->assertEquals(d()->Date('15 марта 2014')->ru_when(d()->Date('16 марта 2014')->stamp), 'Вчера');
		$this->assertEquals(d()->Date('16 марта 2014')->ru_when(d()->Date('16 марта 2014')->stamp), 'Сегодня');
		$this->assertEquals(d()->Date('16 марта 2014')->when(d()->Date('16 марта 2014')->stamp), 'Сегодня');
		$this->assertEquals(d()->Date('16 марта 2013')->when(d()->Date('16 марта 2014')->stamp), '16 марта 2013');
		$this->assertEquals(d()->Date('16 марта 2015')->when(d()->Date('16 марта 2014')->stamp), '16 марта 2015');

		$this->assertEquals(d()->Date('17 марта 2014')->ru_when(d()->Date('today')->stamp), d()->Date('17 марта 2014')->ru_when());
		$this->assertEquals(d()->Date('17 марта 2014')->when(d()->Date('today')->stamp), d()->Date('17 марта 2014')->when());

		$this->assertEquals(d()->Date('15 марта 2013')->ago(d()->Date('today')->stamp), d()->Date('15 марта 2013')->ago());
		$this->assertEquals(d()->Date('15 марта 2013')->ru_ago(d()->Date('today')->stamp), d()->Date('15 марта 2013')->ru_ago());

		$this->assertEquals(d()->Date('24 янв 2014')->to_russian(), '24 января 2014');
		$this->assertEquals(d()->Date('24 янв 2014')->to_english(), 'January 24, 2014');
		
		//Нули
		$this->assertEquals(d()->Date('24 янв 00')->to_russian(), '24 января 2000');
		$this->assertEquals(d()->Date('24 янв 99')->to_russian(), '24 января 1999');
		$this->assertEquals(d()->Date('24 янв 73')->to_russian(), '24 января 1973');
		$this->assertEquals(d()->Date('24 янв 61')->to_russian(), '24 января 1961');
		$this->assertEquals(d()->Date('24 01 61')->to_russian(), '24 января 1961');
		$this->assertEquals(d()->Date('24 01 60')->to_russian(), '24 января 2060');

		$this->assertEquals(d()->Date('январь 12, 60')->to_russian(), '12 января 2060');
		$this->assertEquals(d()->Date('январь 12, 73')->to_russian(), '12 января 1973');

		$this->assertEquals(d()->Date('24 янв 60')->to_russian(), '24 января 2060');

		//Числа, но не таймштамп
		$this->assertEquals(d()->Date('20040302')->to_russian(), '2 марта 2004');
		
		//И по хардкору
		$this->assertEquals(d()->Date('08.31.2015')->to_russian(), '31 августа 2015');

		//Пустые значения
		$this->assertEquals(d()->Date('')->en_user(), '');
		$this->assertEquals(d()->Date('')->tt_user(), '');
		$this->assertEquals(d()->Date('')->tt_user_mini(), '');
		$this->assertEquals(d()->Date(null)->en_user(), '');

		$this->assertEquals(d()->Date('January 24, 2014')->to_russian(), '24 января 2014');
		$this->assertEquals(d()->Date('Jan 24, 2014')->to_russian(), '24 января 2014');

		$this->assertEquals(d()->Date('24 January 2014')->ru_user(), '24 января 2014');
		$this->assertEquals(d()->Date('1 гыйнвар 2014')->ru_user(), '1 января 2014');
		$this->assertEquals(d()->Date('1 гыйн 2014')->ru_user(), '1 января 2014');
		$this->assertEquals(d()->Date('01 гыйнвар 2014')->ru_user(), '1 января 2014');
		$this->assertEquals(d()->Date('01 январь 2014')->ru_user(), '1 января 2014');
		$this->assertEquals(d()->Date('январь 01 2014')->ru_user(), '1 января 2014');
		$this->assertEquals(d()->Date('2000-07-01T00:00:00+00:00')->ru_user(), '1 июля 2000');
		$this->assertEquals(d()->Date('Thu, 21 Dec 2000 16:01:07 +0200')->ru_user(), '21 декабря 2000');
		
		$this->assertEquals(d()->Date('21 Dec 2000')->ru_user(), '21 декабря 2000');
		$this->assertEquals(d()->Date('01 02 03')->ru_user(), '1 февраля 2003');
		$this->assertEquals(d()->Date('01 02 1998')->ru_user(), '1 февраля 1998');
		$this->assertEquals(d()->Date('1 2 3')->ru_user(), '1 февраля 2003');
		$this->assertEquals(d()->Date('1 2 2003')->ru_user(), '1 февраля 2003');
		$this->assertEquals(d()->Date('2001 02 03')->ru_user(), '3 февраля 2001');

		$this->assertEquals(d()->Date('January 23, 2014')->ru_user(), '23 января 2014');
		$this->assertEquals(d()->Date('01 сентябрь 2014')->ru_user(), '1 сентября 2014');
		$this->assertEquals(d()->Date('01 сентябрь 2014')->tt_user(), '1 сентябрь 2014');
		
		$this->assertEquals(d()->Date('01 сентября 2014 23:12:12')->to_mysql(), '2014-09-01 23:12:12');
		$this->assertEquals(d()->Date('02 сентября 2014 23:12')->to_mysql(), '2014-09-02 23:12:00');
		$this->assertEquals(d()->Date('24 янв 99 23:12')->to_mysql(), '1999-01-24 23:12:00');
		
		$this->assertEquals(d()->Date('24 янв 99 23:12')->to_mysql(), '1999-01-24 23:12:00');
		$this->assertEquals(d()->Date('24 янв 99 3:12')->to_mysql(), '1999-01-24 03:12:00');
		$this->assertEquals(d()->Date('02 сентября 2014 1:1')->to_mysql(), '2014-09-02 01:01:00');
		$this->assertEquals(d()->Date('02 сентября 2014 1:1:1')->to_mysql(), '2014-09-02 01:01:01');
		$this->assertEquals(d()->Date('1 2 2003 1:12:1')->to_mysql(), '2003-02-01 01:12:01');
		
		
		
		$this->assertEquals(d()->Date('24 янв 99 каравай хлебца')->to_mysql(), '1999-01-24 12:00:00');
		
		

	}
	
	function test_antispam(){
		//Проверка всякого рода спам-писем.
		//Однозначно "хорошие" сообщения
		$this->assertTrue(antispam('Здравствуйте! Сколько стоит установить окна?'));
		$this->assertTrue(antispam('http://okna-company.ru Здравствуйте! Это наш сайт. Сколько стоит продвижение сайта?'));
		$this->assertTrue(antispam('http://okna-company.ru http://okna-company.ru Здравствуйте! Это наш сайт. Сколько стоит продвижение сайта?'));

		$this->assertTrue(antispam('Здравствуйте! Я хочу заказать машину. Мне нравятся следующие: http://agwdfoywbabsdf.com http://foabakqjahafhasdfasd.com http://baydnkankalkaslasls.com Лето Синий Лис Ягнёнок Бесконечность Бытия Обречённость'));
		
		//Однозначно "плохие" сообщения
		$this->assertFalse(antispam('Online Sex [url=http://agwdfoywbabsdf.com]http://agwdfoywbabsdf.com[/url] online casino.'));
		
		$this->assertFalse(antispam('http://agwdfoywbabsdf.com http://foabakqjahafhasdfasd.com http://baydnkankalkaslasls.com Лето Синий Лис Ягнёнок Бесконечность Бытия Обречённость'));
		
		
		$this->assertFalse(antispam('Courts are a place where serious business is conducted, and that demands appropriate attire, says Delaware Superior Court Judge William Witham Jr., <a href=http://www.ju-this-site-is-fake-mplightfarmsonline.com/new-air-max-2013-run-this-link-is-fake-ning-shoes-for-men-blue-p-2692.html><b>New Air Max 2013 Running Shoes For Men Blue</b></a>, Between the redesign of its denim collection and the launch of a plus size fashion blog, the company has certainly taken strides to provide all women with the awesome denim options they deserve. Check out some of the photos from the look book below, and head to Fashion To Figure to purchase.锘縜'));
		
		
		//Тут письмо подозрительно, так как только английские символы и ссылка. Если есть ссылка и только английские символы - значит спамер.
		$this->assertFalse(antispam('Courts are a place where serious business is conducted, and that demands appropriate attire, says Delaware Superior Court Judge William Witham Jr.,  http://www.ju-this-site-is-fake-mplightfarmsonline.com/new-air-max-2013-run-this-link-is-fake-ning-shoes-for-men-blue-p-2692.html><b>New Air Max 2013 Running Shoes For Men Blue , Between the redesign of its denim collection and the launch of a plus size fashion blog, the company has certainly taken strides to provide all women with the awesome denim options they deserve. Check out some of the photos from the look book below, and head to Fashion To Figure to purchase.锘縜'));
			
			
		$this->assertFalse(antispam('Amdimr <a href=\"http://ydufshhyeuao.com/\">ydufshhyeuao</a>, [url=http://abtzmbcevibp.com/]abtzmbcevibp[/url], [link=http://znaragczxwqp.com/]znaragczxwqp[/link], http://bxueqcscjdwv.com/'));
		
		
		
		
		
		
		
	}
	
}
 
?>