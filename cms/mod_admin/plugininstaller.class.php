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
}
