<?php

d()->singleton('assets',function(){
	
	return new Assets();
});


function stylesheets($params){
	
	$mtime = true;
	$suffix = '.compiled.css';
	$files=array();
	$minify=false;
	$concat=false;
	$is_another_files=false;
	if($params['mtime'] === false){
		$mtime = false;
	}
	if($params['minify']==true || $params['min']==true || $params['optimise']==true || $params['optimize']==true){
		$minify=true;
		$suffix = '.compiled.min.css';
	}
	if($params['concat']==true || $params['optimise']==true || $params['optimize']==true || isset($params['to'])){
		$concat=true;
	}
	$reconcat=false;
	if(isset($params['to'])){
		$concat_file = $params['to'];
		if( $concat_file{0}!='/'){
			$concat_file = '/css/'.$concat_file;
		}
	}else{
		$concat_file = '';
		foreach ($params as $key=>$file){
			if(!is_numeric($key)){
				continue;
			}
			$concat_file .= $file;
		}
		$concat_file = '/css/'.md5($concat_file).'.css';
	}
	foreach ($params as $key=>$file){
	
		if(!is_numeric($key)){
			continue;
		}
		if( $file{0}!='/'){
			$file = '/css/'.$file;
		}
		 
		if(substr($file,-5)=='.scss'){
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].$file.$suffix) || filemtime($_SERVER['DOCUMENT_ROOT'].$file ) > filemtime($_SERVER['DOCUMENT_ROOT'].$file.$suffix) || $is_another_files){
				$reconcat=true;
				if($minify){
					file_put_contents(
						$_SERVER['DOCUMENT_ROOT'].$file.$suffix, //куда записываем
						d()->assets->minify(d()->assets->compile_scss(file_get_contents($_SERVER['DOCUMENT_ROOT'].$file), dirname ($_SERVER['DOCUMENT_ROOT'].$file))) //исходный файл
					);
				}else{
					file_put_contents(
						$_SERVER['DOCUMENT_ROOT'].$file.$suffix, //куда записываем
						d()->assets->compile_scss(file_get_contents($_SERVER['DOCUMENT_ROOT'].$file), dirname ($_SERVER['DOCUMENT_ROOT'].$file)) //исходный файл
					);	
				}
				
				chmod($_SERVER['DOCUMENT_ROOT'].$file.$suffix, 0777);
			}
			$files[]=$file.$suffix;
			
		}elseif(substr($file,-5)=='.less'){
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].$file.$suffix) || filemtime($_SERVER['DOCUMENT_ROOT'].$file ) > filemtime($_SERVER['DOCUMENT_ROOT'].$file.$suffix) || $is_another_files){
				$reconcat=true;
				if($minify){
					file_put_contents(
						$_SERVER['DOCUMENT_ROOT'].$file.$suffix, //куда записываем
						d()->assets->minify(d()->assets->compile_less(file_get_contents($_SERVER['DOCUMENT_ROOT'].$file))) //исходный файл
					);
				}else{
					file_put_contents(
						$_SERVER['DOCUMENT_ROOT'].$file.$suffix, //куда записываем
						d()->assets->compile_less(file_get_contents($_SERVER['DOCUMENT_ROOT'].$file)) //исходный файл
					);	
				}
				chmod($_SERVER['DOCUMENT_ROOT'].$file.$suffix, 0777);
			}
			$files[]=$file.$suffix;
		}elseif(substr($file,-4)=='.css'){
			if($minify){

				if(!file_exists($_SERVER['DOCUMENT_ROOT'].$file.$suffix) || filemtime($_SERVER['DOCUMENT_ROOT'].$file ) > filemtime($_SERVER['DOCUMENT_ROOT'].$file.$suffix) || $is_another_files){
					$reconcat=true;
					file_put_contents(
						$_SERVER['DOCUMENT_ROOT'].$file.$suffix, //куда записываем
						d()->assets->minify( file_get_contents($_SERVER['DOCUMENT_ROOT'].$file)) //исходный файл
					);
				 
					chmod($_SERVER['DOCUMENT_ROOT'].$file.$suffix, 0777);
					
				}
				$files[]=$file.$suffix;
			}else{
				if($concat){
					if(!file_exists($_SERVER['DOCUMENT_ROOT'].$concat_file) || filemtime($_SERVER['DOCUMENT_ROOT'].$file ) > filemtime($_SERVER['DOCUMENT_ROOT'].$concat_file) ){
						$reconcat=true;
					}	 
				}
				
				
				
				$files[]=$file;
			}
		}
		
	}
	
	if($concat){
		$concated='';
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].$concat_file)){
			$reconcat=true;
		}
		if($reconcat){
			foreach ($files as $file){
				$concated.=file_get_contents($_SERVER['DOCUMENT_ROOT'].$file);
			}
			file_put_contents(
				$_SERVER['DOCUMENT_ROOT'].$concat_file, //куда записываем
				$concated //исходный файл
			);
					 
			chmod($_SERVER['DOCUMENT_ROOT'].$concat_file, 0777);
		}
		$files = array($concat_file);
	}
	
	$result = '';
	if($mtime){
		foreach ($files as $file){
			$result .= '<link rel="stylesheet" type="text/css" href="'.$file.'?'.  filemtime($_SERVER['DOCUMENT_ROOT'].$file) .'" />';
		}
	}else{
		foreach ($files as $file){
			$result .= '<link rel="stylesheet" type="text/css" href="'.$file.'" />';
		}
	}
	return $result ;
	
}
