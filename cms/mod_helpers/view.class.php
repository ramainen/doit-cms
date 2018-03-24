<?php

class View
{
	protected $chosen=false;
	
	function render($path){
		 
		$this->chosen = str_replace('..','',$path);//система безопасности =)
		return $this;
	}
	
	function partial($path){
		$this->chosen = str_replace('..','',$path);
		return $this."";
	}
	
	function __toString(){
		$trys = array();
		
		$url=strtok($_SERVER["REQUEST_URI"],'?');
		$url = str_replace('..','',$url);
		$called_file = false;
		
		
		$old_chosen = $this->chosen;
		if($this->chosen !== false){
			$called_file = $this->chosen;
			
			$shortfile = $this->chosen;
			
			$trys[]  = $shortfile ;
			if(is_file($shortfile))
			{
				return  $this->from_file($shortfile);
			}
			
			if(is_file(ROOT . '/app'.$shortfile))
			{
				
 
				return  $this->from_file($shortfile);
			}
			
			if(is_file(ROOT .  $shortfile))
			{
				return  $this->from_file($shortfile, true);
			}
			
			$this->chosen=false;
			
			//Указанно явно варианта недостаточно
			
		}
		
		//Если не указан явно заданный файл, то проводим автопоиск в соответствии с url-ом
		
		//Вариант первый - файл существует
		if($old_chosen!==false && $old_chosen{0} != "/" ){
			$shortfile = $url.'.html';
			$tryfile = ROOT . '/app'.$shortfile;
			
			$trys[] = '/app'.$shortfile;
			
			if( strpos($shortfile,'/_')===false && is_file($tryfile))
			{
				return  $this->from_file($shortfile);
			}
			
		}
		//Вариант третий - index.html
		if(substr($url,-1)=='/'){
			$try_url = substr($url, 0, -1 );
			$shortfile = $try_url.'/index.html';
			$tryfile = ROOT . '/app'.$shortfile;
			
			
			$trys[] = '/app'.$shortfile;
			
			if(is_file($tryfile))
			{
				return  $this->from_file($shortfile);
			}	
		}
		
		
		if($old_chosen!==false && $old_chosen{0} != "/" ){
			//Вариант третий - show.html
			$try_url = substr($url, 0, strrpos($url, '/') );
			$shortfile = $try_url.'/show.html';
			$tryfile = ROOT . '/app'.$shortfile;
			
			$trys[] = '/app'.$shortfile;
			
			if(is_file($tryfile))
			{
				return  $this->from_file($shortfile);
			}
		}
		
		if($called_file===false && $old_chosen===false){
			$try_url = substr($url, 0, strrpos($url, '/') );
			$shortfile = $try_url.'/show.html';
			$tryfile = ROOT . '/app'.$shortfile;
			
			$trys[] = '/app'.$shortfile;
			
			if(is_file($tryfile))
			{
				return  $this->from_file($shortfile);
			}
		}
		if($called_file!==false){
			
			//вариант четвертый - файл внутри директории, вызов closure
			$tryfile =d()->_closure_current_view_path . '/'. $called_file;
			//Вырезаем всё
			$shortfile = substr($tryfile,strlen(ROOT . '/app'));
			$trys[] = '/app'.$shortfile;
			if(is_file($tryfile))
			{
				
				 return  $this->from_file($shortfile);
			}
			
			//вариант пятый - файл внутри директории, вызов route
			if(d()->current_route != false){
				$tryfile =d()->current_route->include_directory . '/'. $called_file;
				//Вырезаем всё
				$shortfile = substr($tryfile,strlen(ROOT . '/app'));
				$trys[] = '/app'.$shortfile;
				if(is_file($tryfile))
				{
					 return  $this->from_file($shortfile);
				}
				
			}
			
		}
		
		
		
		return  print_error_message(' ','',$errfile ,'','Не удалось найти файл шаблона (проверялись: '.implode(', ',$trys).')'  );
	}
	
	function from_file($file, $global=false){

		$name = str_replace(array('/','.','-','\\'),array('_','_','_','_'),substr($file,1)).'_tpl';
	
	if($file == '/app/products/show.html'){
		
		print "NMO";
		exit;
	}
		
		
		


		if(!function_exists($name)){
			
			ob_start(); //Подавление стандартного вывода ошибок Parse Error
			if($global){
				$code = d()->shablonize(file_get_contents(ROOT . $file));
			}else{
				$code = d()->shablonize(file_get_contents(ROOT . '/app'.$file));	
			}
			
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
