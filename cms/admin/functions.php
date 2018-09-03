<?php

/**
 *
 * Проверяет, авторизован ли администратор сайта.
 *
 * @param string $username Имя пользователя
 * @return boolean true, если авторизован
 */
function iam($username=false)
{
	if($username===false){
		return isset($_SESSION['admin']);
	}
	
	return isset($_SESSION['admin']) && $_SESSION['admin'] == $username;
}

function ican($what=false){
	return true;
}

/**
 * $file - сам файл для получения md5
 * $directory - место, где ищем файл, который хотим загрузить
 * $filename - оригинальное имя файла
 * $is_magic - true, если загрузили перетаскиванием или вставкой из буфера, false - если обычная загрузка
 */
 function tinymce_file_upload_findname($file,$directory,$filename,$is_magic){
	if($is_magic){
		return md5_file($file);
	}
	$ext = mb_strtolower(end(explode(".", $filename)), 'UTF-8' );
	$filename = substr($filename,0, strrpos($filename,'.')) ;
	$new_filename =  tinymce_file_upload_transliterate_file_name($filename);
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
				$new_filename = $first_part . '-'. (1*$lasttire + 1);
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

 function tinymce_file_upload_transliterate_file_name($string)
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