<?php

d()->singleton('assets',function(){
	
	return new Assets();
});


function stylesheets($params){
	
	//demo; в дальнейшем будет расширено
	$file=$params[0];
	
	if( $file{0}!='/'){
		$file = '/css/'.$file;
	}
	
	$is_another_files=false;
 
	
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].$file.'.compiled.min.css') || filemtime($_SERVER['DOCUMENT_ROOT'].$file ) > filemtime($_SERVER['DOCUMENT_ROOT'].$file.'.compiled.min.css') || $is_another_files){
		
		file_put_contents(
			$_SERVER['DOCUMENT_ROOT'].$file.'.compiled.min.css', //куда записываем
			d()->assets->compile_postcss(file_get_contents($_SERVER['DOCUMENT_ROOT'].$file)) //исходный файл
		);
		
		
		
		
		chmod($_SERVER['DOCUMENT_ROOT'].$file.'.compiled.min.css', 0777);
	}
	return '<link rel="stylesheet" type="text/css" href="'.$file.'.compiled.min.css" />';
	
	
	
}
