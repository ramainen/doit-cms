<?php
error_reporting(0);
header("Content-Type: text/plain; charset=utf-8");


 function transliterate_file_name($string)
{
	$converter = array(

		'а' => 'a',   'б' => 'b',   'в' => 'v', 'г' => 'g',   'д' => 'd',   'е' => 'e',
		'ё' => 'e',   'ж' => 'zh',  'з' => 'z',  'и' => 'i',   'й' => 'y',   'к' => 'k',
		'л' => 'l',   'м' => 'm',   'н' => 'n', 'о' => 'o',   'п' => 'p',   'р' => 'r',
		'с' => 's',   'т' => 't',   'у' => 'u',  'ф' => 'f',   'х' => 'h',   'ц' => 'c',
		'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',   'ь' => '',  'ы' => 'y',   'ъ' => '',
		'э' => 'e',   'ю' => 'yu',  'я' => 'ya', 'А' => 'A',   'Б' => 'B',   'В' => 'V', 'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
		'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z', 'И' => 'I',   'Й' => 'Y',   'К' => 'K',
		'Л' => 'L',   'М' => 'M',   'Н' => 'N',  'О' => 'O',   'П' => 'P',   'Р' => 'R',
		'С' => 'S',   'Т' => 'T',   'У' => 'U',  'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
		'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch', 'Ь' => '',  'Ы' => 'Y',   'Ъ' => '',
		'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',  ' ' => '-',  ',' => '-',  '.' => '-',  '/' => '-'
	);

	$str =  strtr($string, $converter);
	$str = strtolower($str);

	$str = preg_replace('~[\s\t\_]+~u', '-', $str);
	$str = preg_replace('~-+~u', '-', $str);
	$str = preg_replace('~[^-a-z0-9_]+~u', '', $str);
	$str = trim($str, "-");

	return $str;
    
}
 /**
 * $file - сам файл для получения md5
 * $directory - место, где ищем файл, который хотим загрузить
 * $filename - оригинальное имя файла
 * $is_magic - true, если загрузили перетаскиванием или вставкой из буфера, false - если обычная загрузка
 */
 function findname($file,$directory,$filename,$is_magic){
	 if($is_magic){
		 return md5_file($file);
	 }
	 $ext = strtolower( substr($filename,strrpos($filename,'.')+1) );
	 $filename = substr($filename,0, strrpos($filename,'.')) ;
	 $new_filename =  transliterate_file_name($filename);
	 //Проверка, если файл уже существует
	 if($new_filename==''){
		 return md5_file($file);
	 }
	 //если файл существует, и он такой же, что етсь и сейчас
	 if(is_file($directory.'/'.$new_filename.'.'.$ext) && (md5_file($directory.'/'.$new_filename.'.'.$ext) == md5_file($file)) ){
		return $new_filename;
	 }
	 //если файл существует, и он отличается от того, что есть уже сейчас
	 while(is_file($directory.'/'.$new_filename.'.'.$ext) && (md5_file($directory.'/'.$new_filename.'.'.$ext) != md5_file($file)) ){
		 //файл существует, опредеяем новый
		 // есть тирешки в имени?
		 if(strpos($new_filename,'-')!==false){
			 $lasttire = substr(strrchr($new_filename,'-'),1);
			 //содержит только числа?
			 if(preg_match('#^[0-9]+$#ui',$lasttire)){
				//увеличиваем то, что в конце
				$first_part = substr($new_filename,0,-1 * strlen($lasttire) -1 );
			//	var_dump($new_filename);
				$new_filename = $first_part . '-'. (1*$lasttire + 1);
			//	var_dump($lasttire);
			//	var_dump($first_part);
			//	var_dump($new_filename);
			//	exit;
			 }else{
				 //есть буквы, приписываем число в конец
				 $new_filename = $new_filename . '-1';
			 }
		 }else{
			//тирешек нет - приписываем
			$new_filename = $new_filename . '-1';
		 }
	 }
	 
	 return $new_filename;
 }
 
 
class tinyimages {
 
        var $filesendname='Filedata';
	var $folder = '/storage';

	function __construct() {
		define(DIR, $_SERVER['DOCUMENT_ROOT'].'/');

		// ATTENTION!
		// You need to check the session here, because methods of this class can be danger to security!
		//if(!isset($_SESSION['user']['type'])) return false;
                
		$this->folder = $this->folder;
		$this->restrict = $this->folder;
            
	}



	function UploadFiles() {
	global $ioptions,$filesendname;
      ;
		$our_folder = $this->folder;


		if($_GET['uri'] != '') {
			$this->folder = $_GET['uri'];
			if(!file_exists(DIR.$this->folder))
					mkdir(DIR.$this->folder);
			$realpath1 = realpath(DIR.$our_folder);
			$realpath2 = realpath(DIR.$_GET['uri']);

			$strlen1 = strlen($realpath1);
			$strlen2 = strlen($realpath2);

			if($strlen1 > $strlen2) { page404(); exit(); }
			for($i=0;$i<$strlen1;$i++) {
				if($realpath1[$i] != $realpath2[$i]) { page404(); exit(); }
			}
		}


		$result = array();
      
		if (isset($_FILES['Filedata'])) {
              
			$file = $_FILES['Filedata']['tmp_name'];
			$error = false;
			$size = false;

			/*if (!is_uploaded_file($file)  )  {
				 

			}
			else {*/

			if (!is_uploaded_file($file)) {
				print "error1";
                                exit();
			} else
			if (false ) {
					print "error2";
                                        
                                exit();
			} else
			if (!preg_match('/\.(mp3|zip|txt|flv|doc|rtf|swf|docx|xlsx|xml|ies|pdf|zip|rar|xls|jpg|gif|png|jpeg|pptx?)$/i', $_FILES['Filedata']['name']) ) {
				print "ERROR_Invalid_filetype";
                                exit();
			} else  {
				
				$newadress="/storage";
				$newfilename= $name.'.'.$ext;
				if(isset ($_GET['uri']))$newadress=$_GET['uri'];


				$ext = substr($_FILES['Filedata']['name'],strrpos($_FILES['Filedata']['name'],'.')+1);
				$name = findname($_FILES['Filedata']['tmp_name'],DIR.$this->folder,$_FILES['Filedata']['name'],false);
				$source = DIR.$this->folder.'/'.$name.'.'.$ext;

				if(!copy($_FILES['Filedata']['tmp_name'], $source)) {
					print "error4";
				} else {

			
					$result['result'] = 'success';
					$newadress="/storage";
					$newfilename= $name.'.'.$ext;
					if(isset ($_GET['uri']))$newadress=$_GET['uri'];
					if($newadress=="")$newadress="/storage";
						print  $newadress."/".$newfilename;
 
				}
			}
		}
		else {
			print 'error5';
			}
		

		/*foreach ($result as $key=>$val) {
			$return[$key] = iconv("windows-1251", "utf-8", $val);
		}*/


		 
		exit();
	}
 


	
}

 
$images = new tinyimages();
$images->UploadFiles();
