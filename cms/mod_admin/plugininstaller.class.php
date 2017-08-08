<?php


class PluginInstaller extends UniversalSingletoneHelper
{
	public $tmp_folder='storage';
	public $download_url='http://plugins.doit-cms.ru/get/';
	function install($plugin_folder)
	{
		$zip = new ZipArchive;
		if ($zip->open( $_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/'.$plugin_folder.'.zip') === TRUE) {
			$zip->extractTo($_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/'.$plugin_folder);
			$zip->close();
			
			
			$standart_format=true;
			$search_started=false;
			$dir = $_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/'.$plugin_folder;
			$prefixlength = strlen($dir)+1;
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),
										  RecursiveIteratorIterator::SELF_FIRST);
			foreach ($iterator as $path) {
			
		
				
				
				$file=substr($path->__toString(),$prefixlength);
				$plugin_file=$file;
				$app_file=$file;
				
				if($search_started==false){
					$search_started=true;
					if($file==$plugin_folder){
						$standart_format=false;
						continue;
					}
				}
				
				$search_started=true; 
				
				if($standart_format==false){
					$app_file = substr($app_file,strlen($plugin_folder)+1);
				}
				
				if ($path->isDir()) {
					//СОздание отсуствующих директорий
					if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$app_file)){
						mkdir($_SERVER['DOCUMENT_ROOT'].'/'.$app_file);
						chmod($_SERVER['DOCUMENT_ROOT'].'/'.$app_file, 0777);
					}
				} else {
					if(substr($plugin_file,-5)=='.diff'){
						$file_without_diff=substr($plugin_file,0,-5);
						
						$patch=file_get_contents($dir.'/'.$plugin_file);
						$first_nl = strpos($patch,"\n");
						$search_string=substr($patch,0,$first_nl);
						$search_string=str_replace("\r","",$search_string);
						
						$replacement_string=substr($patch,$first_nl);

						
						$first_file=file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'.$file_without_diff);
						
						$first_file=preg_replace('/('.$search_string.')/i',"$1".$replacement_string,$first_file,1);
						file_put_contents ( $_SERVER['DOCUMENT_ROOT'].'/'.$file_without_diff,$first_file);
						chmod($_SERVER['DOCUMENT_ROOT'].'/'.$file_without_diff, 0777);
						
					} else {
						copy($dir.'/'.$plugin_file,  $_SERVER['DOCUMENT_ROOT'].'/'.$app_file);
						chmod($_SERVER['DOCUMENT_ROOT'].'/'.$app_file, 0777);
					}
					
				}
			}
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),
                                              RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($iterator as $path) {
				if ($path->isDir()) {
					rmdir($path->__toString());
				} else {
					unlink($path->__toString());
				}
			}
		
			rmdir($dir);
		}
			
		unlink($dir.'.zip');
	}
	
	function download_and_install_pack($plugins){
		
		$url = 'http://plugins.doit-cms.ru/2.0/dl.php?';
		$urls=array();
		foreach($plugins as $value){
			$urls[]= 'modules[]='.$value;
		}
		$url.= implode('&', $urls);
		$result=$_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/tmp_plugins.zip';
		 
		file_put_contents($result,file_get_contents($url));
		
		
		
		$zip = new ZipArchive;
		if ($zip->open($result) === TRUE) {
			$zip->extractTo($_SERVER['DOCUMENT_ROOT'].'/app');
			$zip->close();
		}
			
		 unlink($result);
		
		
	}
	
	
	function download($file_name)
	{
		$url=$this->download_url . $file_name .'.zip';
		
		$result=$_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/'.$file_name.'.zip';
		file_put_contents($result,file_get_contents($url));
	}
	
	function get_list()
	{
		return json_decode(file_get_contents( $this->download_url));
	}
	
	
	function update_cms()
	{
		$ok=true;
		$name = 'cms'.date('Y-m-d-').time();
		
		$url='http://github.com/ramainen/doit-cms/zipball/master';
		$result=$_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/'.$name .'.zip';
		file_put_contents($result,file_get_contents($url));
		chmod($result, 0777);
		 
		
		$zip = new ZipArchive;
		if ($zip->open( $result) === TRUE) {
			$zip->extractTo($_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/'.$name);
			chmod($_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/'.$name, 0777);
			
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/'.$name),
                                              RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($iterator as $path) {
				chmod($path->__toString() , 0777);
			}
			
			
			$zip->close();
			
			
			$container = '';
			$dir_reader = opendir($_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/'.$name);
				while($file = readdir($dir_reader)){
					if ($file != '.' && $file != '..'){
						$container = $file;
					}
				}
				if($container == ''){
					print 'Обновление завершилось неудачей  - в архиве не найдена система';
					exit();
				}

			if($ok==false){
				return false;
			}
			
			$new_name = $_SERVER['DOCUMENT_ROOT'].'/cms'.date('Y-m-d').'-'.time();
			if(!rename($_SERVER['DOCUMENT_ROOT'].'/cms',$new_name)){
				print 'Обновление завершилось неудачей на этапе переименования папки cms в резервную копию';
				return false;
			}
			
			if($ok==false){
				return false;
			}
			
			//Эту папку можно сохранить при желании
			d()->renamed_cms = $new_name;
			$_SESSION['renamed_cms']=  $new_name;
			if(!copy_r($_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/'.$name . '/' . $container . '/cms' , $_SERVER['DOCUMENT_ROOT'].'/cms' )) {
				$ok=false;
				print 'Обновление завершилось неудачей на этапе копирования папки cms в корень сайта';
				exit();
			}
			
			if($ok==false){
				return false;
			}
			
			
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/'.$name),
                                              RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($iterator as $path) {
				if ($path->isDir()) {
					rmdir($path->__toString());
				} else {
					unlink($path->__toString());
				}
			}
		
			rmdir($_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/'.$name);
			unlink($_SERVER['DOCUMENT_ROOT'].'/'.$this->tmp_folder.'/'.$name .'.zip');
			
		}else{
			print 'Обновление завершилось неудачей на этапе распаковки архива';
			return false;
		}
		return $ok;
	}
	
	
	 

}

function copy_r( $path, $dest ) {
	if( is_dir($path) ) {
		if(!mkdir( $dest )){
			print "Не удаётся создать директорию ".$dest ;
			return false;
		}
		chmod($dest , 0777);
		$objects = scandir($path);
		if( sizeof($objects) > 0 ) {
		
			foreach( $objects as $file ) {
				if( $file == "." || $file == ".." ) {
					continue;
				}
				
				if( is_dir( $path.DIRECTORY_SEPARATOR.$file ) ) {
					copy_r( $path.DIRECTORY_SEPARATOR.$file, $dest.DIRECTORY_SEPARATOR.$file );
				} else {
					copy( $path.DIRECTORY_SEPARATOR.$file, $dest.DIRECTORY_SEPARATOR.$file );
					chmod($dest.DIRECTORY_SEPARATOR.$file  , 0777);
				}
			}
		}
		return true;
	} elseif( is_file($path) ) {
		$fl	= copy($path, $dest);
		chmod($dest  , 0777);
		if(!$fl){
			print "Не удаётся скопировать файл ".$dest ;
		}
		return $fl;
	} else {
		print "Не знаю, чем вы там занимаетесь, но Вы копируете не файл и не директорию (".$path  . ' копируется в '. $dest.') ';
		return false;
	}
}