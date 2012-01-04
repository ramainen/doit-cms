<?php
	
class Test
{
	public $ok_results=0;
	public $bad_results=0;
	public $bad_tests=array();
	public $current_test='';
	public $last_nonequal='';
	public $current_sub_test=0;
	public function anotherTest()
	{
		$this->current_sub_test++;
		$this->last_nonequal='';
	}
	
	public function goodTest()
	{
		$this->ok_results++;
	}	
	
	public function failedTest()
	{
		$this->bad_results++;
		$debug=debug_backtrace();
		
		$this->bad_tests[]=' '.$debug[1]['function'].' on '.get_class ($this).'->'.$this->current_test.': проверка №'.$this->current_sub_test.': строка '.$debug[1]['line'].$this->last_nonequal;
		
	}
	
	public function assertEquals($var1,$var2)
	{
		$this->anotherTest();
		if($var1==$var2){
			$this->goodTest();
		}else{
			$this->last_nonequal = '<br><span style="color:green">"'.htmlspecialchars($var1).'"</span> != <span style="color:blue">"'.htmlspecialchars($var2).'"</span>';
			$this->failedTest();
		}
	}
	
	public function assertNonSpaceEquals($var1,$var2)
	{
		$var1=str_replace(' ','',$var1);
		$var1=str_replace("\n",'',$var1);
		$var1=str_replace("\r",'',$var1);
		$var1=str_replace("\t",'',$var1);
		
		$var2=str_replace(' ','',$var2);
		$var2=str_replace("\n",'',$var2);
		$var2=str_replace("\r",'',$var2);
		$var2=str_replace("\t",'',$var2);
		
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
		$color='green';
		if($this->bad_results!=0){
			$color='red';
		}
		print '<span style="color:'.$color.'">';
		print "OK: {$this->ok_results}, BAD: {$this->bad_results} </span><br>";
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