<?php
	
class HelpersTest extends Test
{
	
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
	
	
	
}
 
?>