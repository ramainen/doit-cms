<?php

class View
{
	protected $chosen=false;
	
	function render($path){
		$this->chosen = $path;
		return $this;
	}
	
	function partial($path){
		$this->chosen = $path;
		return $this;
	}
	
	function __toString(){
		
		$url=strtok($_SERVER["REQUEST_URI"],'?');
		$url = str_replace('..','',$url);//система безопасности =)
		
		if($this->chosen !== false){
			$shortfile = $this->chosen;
			$this->chosen=false;
			return  $this->from_file($shortfile);
		}
		
		//Вариант первый - файл существует
		$shortfile = $url.'.html';
		$tryfile = ROOT . '/app'.$shortfile;
		if( strpos($shortfile,'/_')===false && is_file($tryfile))
		{
			return  $this->from_file($shortfile);
		}
		//Вариант третий - index.html
		if(substr($url,-1)=='/'){
			$try_url = substr($url, 0, -1 );
			$shortfile = $try_url.'/index.html';
			$tryfile = ROOT . '/app'.$shortfile;
			if(is_file($tryfile))
			{
				return  $this->from_file($shortfile);
			}	
		}
		
		//Вариант третий - show.html
		$try_url = substr($url, 0, strrpos($url, '/') );
		$shortfile = $try_url.'/show.html';
		$tryfile = ROOT . '/app'.$shortfile;
		if(is_file($tryfile))
		{
			return  $this->from_file($shortfile);
		}		
		
	}
	
	function from_file($file){
		$name = str_replace(array('/','.','-','\\'),array('_','_','_','_'),substr($file,1)).'_tpl';
	//		function get_compiled_code($fragmentname)
	
		
		
		


		if(!function_exists($name)){
			
			ob_start(); //Подавление стандартного вывода ошибок Parse Error
			$code = d()->shablonize(file_get_contents(ROOT . '/app'.$file));
			$result=eval('function '.$name.'(){ $doit=d(); ?'.'>'.$code.'<'.'?php ;} ');
			ob_end_clean();
			if ( $result === false && ( $error = error_get_last() ) ) {
 				$lines = explode("\n",'function '.$name.'(){ $doit=d(); ?'.'>'.$code.'<'.'?php ;} ');
				$file = $this->fragmentslist[$name];
				return print_error_message( $lines [$error['line']-1],$error['line'],$file,$error['message'],'Ошибка при обработке шаблона',true);
			} else {
				ob_start();
				$result =  call_user_func($name);
				$_end = ob_get_contents();
				ob_end_clean();
				if (!is_null($result)) {
					$_end = $result;
				}
				return $_end;
			}


		}else{
			ob_start();
			$result =  call_user_func($name);
			$_end = ob_get_contents();
			ob_end_clean();
			if (!is_null($result)) {
				$_end = $result;
			}
			return $_end;
		}

	
	}
}
