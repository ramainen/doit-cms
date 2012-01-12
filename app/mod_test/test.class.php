<?php
	
class Test
{
	public $ok_results=0;
	public $bad_results=0;
	public $bad_tests=array();
	public $current_test='';
	public $last_nonequal='';
	public $coverages=array();
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

	public function assertCoverage($file)
	{
		if(substr($file,0,1)!='/'){
			$file='/'.$file;
		}
		$this->coverages[$file]=true;

	}
	public function assertEquals($var1,$var2)
	{
		$this->anotherTest();
		if($var1==$var2){
			$this->goodTest();
		}else{
			$this->last_nonequal = '<span style="color:green">"'.htmlspecialchars($var1).'"</span> != <span style="color:blue">"'.htmlspecialchars($var2).'"</span>';
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
	
	//Заглушка для запуска сразу всех тестов
	public function runAll()
	{
//		var_dump( );
		
		foreach(d()->php_files_list as $key=>$value){
			if(substr($key,-10)=='test_class' && $key!='test_class'){
				include_once($value);
			}
		} 
		
		foreach(get_declared_classes() as $key=>$value){
			if(substr($value,-4)=='Test' && $value != 'Test'){
				d()->$value->run();
			}
		}
		
		
	}
	
	public function run()
	{
		$COLOR_RED='#FFDCD7';
		$COLOR_GREEN='#A5FFA5';
		if(function_exists('xdebug_start_code_coverage')){
			xdebug_start_code_coverage(XDEBUG_CC_UNUSED);
		}


		foreach( get_class_methods($this) as $method){
			if(substr($method,0,4)=='test') {
				$this->current_test=$method;
				$this->current_sub_test=0;
				$this->$method();
			}
		}
		print '<pre style="margin:0;">';
		print get_class($this).': ';
		$color='green';
		if($this->bad_results!=0){
			$color='red';
		}
		print '<span style="color:'.$color.'">';
		print "OK: {$this->ok_results}, FAILS: {$this->bad_results} </span><br>";
		foreach($this->bad_tests as $test){
			print 'fail: '.$test.'<br>';
		}
		print '</pre>';
		if(function_exists('xdebug_start_code_coverage')){
			$files_coverage=xdebug_get_code_coverage();
			$right_slashes=str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']);
			foreach($files_coverage as $key=>$value){

				$file=str_replace('\\','/',$key);
				$file=str_replace($right_slashes,'',$file);
				$file=str_replace('\\','/',$file);
				if(isset($this->coverages[$file])){
	//				print $file;
				//	var_dump($value);
					$lines=file($key);
					$covered_lines=array();
					$uncovered_lines=array();
					$dead_lines=array();
					foreach($value as $line=>$_count){
						if($_count>0){
							//print $lines[$line-1]."\n";
							$covered_lines[]=$line-1;
						}else{
							if($_count==-1){
								if(trim($lines[$line-1])=='}' || (1==preg_match('/^\s*}\s*else\s*{\s*$/',$lines[$line-1]))){
									$covered_lines[]=$line-1;
								}else{
									$uncovered_lines[$line-1]=$lines[$line-1];
								}

							}
							if($_count==-2){
								$dead_lines[$line-1]=$lines[$line-1];
							}


						}

					}
					$covered_count=count($lines)-count($uncovered_lines);
					$procent = ceil(($covered_count/count($lines))*100);
					if(count($uncovered_lines)>0){
						$rand_id=md5(rand().time());
						print '<pre  style="margin:0;">Файл '.$file.' покрыт на '.$procent .'% ('.$covered_count.' из '.count($lines).', непокрыто: '.
							count($uncovered_lines).')'. //, из них рабочего кода  '.count($covered_lines).
							' <a href="#" onclick="document.getElementById(\''. 'id_' .$rand_id . '\').style.display=\'block\';return false;" >показать</a></pre>';
						print '<div id="id_'.$rand_id.'" style="display:none;border:1px solid #ffee99;padding-left:15px;"><pre  style="margin:0;">';

						$max=1;
						foreach($uncovered_lines as $_key=>$_value){
							$max=$_key; //Ага,быдлокод
						}
						$dots_shown=false;
						for($i=0;$i<=$max;$i++){

							if (isset($uncovered_lines[$i])){
								$dots_shown=false;
								print '<div style="background:'.$COLOR_RED.'">'.($i+1).':   '. (htmlspecialchars($uncovered_lines[$i]))."</div>";
							}elseif(isset($uncovered_lines[$i+1])){
								$dots_shown=false;
								print '<div style="background:'.$COLOR_GREEN.'">'.($i+1).':   '. (htmlspecialchars($lines[$i]))."</div>";
							}elseif(isset($uncovered_lines[$i+2]) && $dots_shown==true && trim($lines[$i])!=''){
								$dots_shown=false;
								print '<div style="background:'.$COLOR_GREEN.'">'.($i+1).':   '. (htmlspecialchars($lines[$i]))."</div>";
							}elseif(isset($uncovered_lines[$i-1])){
								$dots_shown=false;
								print '<div style="background:'.$COLOR_GREEN.'">'.($i+1).':   '. (htmlspecialchars($lines[$i]))."</div>";
							}elseif($dots_shown==false){
								$dots_shown=true;
								print '<div  style="font-size:8px;">...</div>';
							}
						}

						print "</div></pre>";
					}else{
						print "<pre  style='margin:0;'>Файл $file покрыт на 100%</pre>";
					}
				}else{

				}
			}
		}else{
			if(count($this->coverages)>0){
				print '<pre  style="margin:0;">Для подсчёта процента покрытия установите xdebug.</pre>';
			}
		}



	//	var_dump();
	}
	
	public function test_itself()
	{
		$this->assertEquals(1,1);
	}
	
}
 
?>