<?php
	
class Test
{
	public $ok_results=0;
	public $bad_results=0;
	public $bad_tests=array();
	public $current_test='';
	public $current_sub_test=0;
	public function anotherTest()
	{
		$this->current_sub_test++;
	}
	
	public function goodTest()
	{
		$this->ok_results++;
	}	
	
	public function failedTest()
	{
		$this->bad_results++;
		$debug=debug_backtrace();
		
		$this->bad_tests[]=' '.$debug[1]['function'].' on '.get_class ($this).'->'.$this->current_test.': проверка #'.$this->current_sub_test.': строка '.$debug[1]['line'];
		
	}
	
	public function assertEquals($var1,$var2)
	{
		$this->anotherTest();
		if($var1==$var2){
			$this->goodTest();
		}else{
			$this->failedTest();
		}
	}
	
	public function assertTrue($var1)
	{
		$this->anotherTest();
		if($var1){
			$this->goodTest();
		}else{
			$this->failedTest();
		}
	}
	
	public function assertFalse($var1)
	{
		$this->anotherTest();
		if(!$var1){
			$this->goodTest();
		}else{
			$this->failedTest();
		}
	}
	
	public function assertNotEquals($var1,$var2)
	{
		$this->anotherTest();
		if($var1!=$var2){
			$this->goodTest();
		}else{
			$this->failedTest();
		}
	}	
	
	public function run()
	{
		foreach( get_class_methods($this) as $method){
			if(substr($method,0,4)=='test') {
				$this->current_test=$method;
				$this->current_sub_test=0;
				$this->$method();
			}
		}
		print '<pre>';
		print "OK: {$this->ok_results}, BAD: {$this->bad_results} <br>";
		foreach($this->bad_tests as $test){
			print 'fail: '.$test.'<br>';
		}
		print '</pre>';
	}
	
	public function test_itself()
	{
		$this->assertEquals(1,1);
	}
	
}
 
?>