<?php
	
class HelpersTest extends Test
{
	
	public function test_helperh()
	{
		$this->acceptEqual('asd',d()->h('asd'));
		$this->acceptNotEqual('ssd',d()->h('asd'));
	}
	
	function test_valid_email()
	{
		$this->acceptTrue(d()->valid_email('user@mail.ru'));
		$this->acceptTrue(d()->valid_email('__tony@mail.ru'));
		$this->acceptFalse(d()->valid_email('@__tony@mail.ru'));
		$this->acceptFalse(d()->valid_email('mail.ru'));
		$this->acceptFalse(d()->valid_email('a@a.a'));
		$this->acceptFalse(d()->valid_email('троглодит@почта.рф'));
		$this->acceptTrue(d()->valid_email('a@a.ru'));
	}
	
	function test_declOfNum()
	{
		$this->acceptEqual(d()->declOfNum(4,array('попугай','попугая','попугаев')),'попугая');
		$this->acceptNotEqual(d()->declOfNum(4,array('попугай','попугая','попугаев')),'попугаев');
		$this->acceptEqual(d()->declOfNum(11,array('попугай','попугая','попугаев')),'попугаев');
		$this->acceptEqual(d()->declOfNum(111,array('попугай','попугая','попугаев')),'попугаев');
		$this->acceptEqual(d()->declOfNum(0,array('попугай','попугая','попугаев')),'попугаев');
		$this->acceptEqual(d()->declOfNum(81,array('попугай','попугая','попугаев')),'попугай');
		$this->acceptEqual(d()->declOfNum('стопицот',array('попугай','попугая','попугаев')),'попугаев');
		
	}
	function test_preview()
	{
		//тестируем превью
		$this->acceptEqual(d()->preview('/storage/1.gif'),'/storage/.thumbs/preview1_1.gif');
		$this->acceptEqual(d()->preview(''),'');
		$this->acceptEqual(d()->preview('/storage/'),'');
		$this->acceptEqual(d()->preview('/storage/folder/2.gif'),'/storage/folder/.thumbs/preview1_2.gif');
		$this->acceptEqual(d()->preview('/storage/folder/2.jpeg'),'/storage/folder/.thumbs/preview1_2.jpeg');
		$this->acceptEqual(d()->preview('/storage/folder/2.GIF'),'/storage/folder/.thumbs/preview1_2.GIF');
		$this->acceptEqual(d()->preview('нет картинки'),'');
		
		
	}
}
 
?>