<?php
	
class ActiveRecordTest extends Test
{

	//Проверка d()->Page->paginate(10,[$_GET['page']);
	function test_paginate()
	{
		
		//Проверка правильности лимита
		$count_all_first =  d()->Page->count;
		
		$page=d()->Page->paginate(5,2);
		$count_all =  $page->count;
		$found_rows =  $page->found_rows;
		
		
		//Общее количество строк == результирующее количетсов строк
		$this->assertTrue($found_rows==$count_all_first);
		$this->assertTrue($count_all==5);
		
		$tmp=$_GET;
		$_GET['page']=1;
		//Тестируем внешние интерфейсы для передачи пагинатору
		$page=d()->Page->paginate(5);
		$this->assertTrue($page->current_page==1);
		$this->assertTrue($page->per_page==5);
		
		
		//Тестируем пагинатор
		$all_elements = $page->found_rows;
		$current_page = 1;
		$per_page = 5;
		
		
		
		$all_pages= ceil($all_elements/$per_page);
		
		$pages_etalon=d()->Paginator->generate($all_pages,$current_page);
		
		$pages_generated=d()->Paginator->generate($page);
		
		//Пагинатор с обычными данными и пагинатор на основе класса должны вести себя одинаково
		$this->assertEquals($pages_etalon ,$pages_generated);
		
		
		//Пагинатор, вызываемый изнутри класса
		$page=d()->Page->paginate(3);
		$etalon_paginator = d()->Paginator->generate($page);
		
		$page2=d()->Page->paginate(3);
		$generated_paginator = $page2->paginator;
		$this->assertEquals($etalon_paginator ,$generated_paginator);
		
		
		
		//Пагинатор с классов
		$page=d()->Page->paginate(3);
		$etalon_paginator = d()->Paginator->setActive('red')->generate($page);
		
		$page2=d()->Page->paginate(3);
		$generated_paginator = $page2->paginator;
		
		//Заведомо ошибочно
		$this->assertNotEquals($etalon_paginator ,$generated_paginator);
		
		
		
		//Пагинатор с классов
		$page=d()->Page->paginate(3);
		$etalon_paginator = d()->Paginator->setActive('red')->generate($page);
		
		$page2=d()->Page->paginate(3);
		$generated_paginator = $page2->paginator('red');
		$this->assertEquals($etalon_paginator ,$generated_paginator);
		
		
	 
		$_GET = $tmp;
	}
}
 
?>