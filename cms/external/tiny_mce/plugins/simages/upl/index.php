<?php
 /**
 * Based on Images Manager server code by Antonov Andrey, dustweb.su
 */

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
	 //$ext = strtolower( substr($filename,strrpos($filename,'.')+1) );
	 $ext = mb_strtolower(end(explode(".", $filename)), 'UTF-8' );
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
 error_reporting(0);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-Type: text/html; charset=Windows-1251");
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
 <style type="text/css">
 body, html
 {   margin:0;
    height:21px;
    padding:0;
    overflow:hidden;
    border:0px;
    border:0;
  border:none;
 background: url(icon.gif) no-repeat left top;
 }
        #File1
        {
            position: absolute;
        }
        .customFile
        {
            width: 219px;
            margin-left: -160px;
            cursor: default;
            height: 60px;
            z-index: 2;
            filter: alpha(opacity: 0);
            opacity: 0;
        }
        .fakeButton
        {
            position: absolute;
            z-index: 1;
            width: 85px;
            height: 21px;
            background: url(icon.gif) no-repeat left top;
            float: left;
        }
       
        .blocker
        {
            position: absolute;
            z-index: 3;
            width: 150px;
            height: 21px;
            background: url(/cms/external/pixel.gif);
            margin-left: -155px;
        }
        #FileName
        {
            position: absolute;
            height: 15px;
            margin-left: 90px;
            font-family: Verdana;
            font-size: 8pt;
            color: Gray;
            margin-top: 2px;
            padding-top: 1px;
            padding-left: 19px;
        }
        #activeBrowseButton
        {
            display: none;
        }
    </style>

</head>
<body>
<?php




	function UploadFiles() {
	global $ioptions;

		$our_folder = $_SERVER["DOCUMENT_ROOT"]."/storage";

		$result = array();

		if (isset($_FILES['image']) || !empty($_POST['base64'])) {
			if(!empty($_POST['base64'])){
				 
				
				$file = '/storage/tmp_base64.png';
				list($type, $data) = explode(';', $_POST['base64']);
				list(, $data)      = explode(',', $data);
				$data = base64_decode($data);
				$file = $_SERVER['DOCUMENT_ROOT'].'/storage/tmp_base64.png';
				file_put_contents($file, $data);
				$filename = 'blob.png';
				$is_magic = true;

			}else{
				$file = $_FILES['image']['tmp_name'];
				$filename = $_FILES['image']['name'];
				$is_magic = false;
			}
			
			$error = false;
			$size = false;
			
			$is_uploaded =   ( !empty($_POST['base64']) || is_uploaded_file($file));
			if (!$is_uploaded /*|| ($_FILES['image']['size'] > 2 * 1024 * 1024)*/ ) {
				if($_GET['lng']=='ru') {
					$error = 'Пожалуйста, загружайте файлы не более 2Мб!';
				} else {
					$error = 'Please do not upload files over 2Mb!';
				}
			} else
			if (!$error && !($size = @getimagesize($file) ) ) {
				if($_GET['lng']=='ru') {
					$error = 'Ошибка, не верный тип файла';
				} else {
					$error = 'Error, unsupported type of file';
				}
			} else
			if (!$error && !in_array($size[2], array(1, 2, 3, 7, 8) ) ) {
				if($_GET['lng']=='ru') {
					$error = 'Ошибка типа файла, рекомендуется загружать файлы JPEG';
				} else {
					$error = 'Error type of file, recommend upload JPEG files';
				}
			} else
			if (!$error && ($size[0] < 5) || ($size[1] < 5)) {
				if($_GET['lng']=='ru') {
					$error = 'Пожалуйста, загружайте картинки размером более 5px.';
				} else {
					$error = 'Please upload pictures larger than 5px.';
				}
			}
			if ($error) {
				$result['result'] = 'failed';
				$result['error'] = $error;
			}
			else {
				//$ext = strtolower( substr($filename,strrpos($filename,'.')+1) );
				$ext = mb_strtolower(end(explode(".", $filename)), 'UTF-8' );
				$name = findname($file,$our_folder,$filename,$is_magic);
				$source = $our_folder.'/'.$name.'.'.$ext;
				
				if(!copy($file, $source)) {
					$result['result'] = 'error';
					if($_GET['lng']=='ru') {
						$result['error'] = 'Ошибка при копировании файла!';
					} else {
						$result['error'] = 'Failed to copy a file!';
					}
				} else {
			
					if(!file_exists($our_folder.'/.thumbs')) mkdir($our_folder.'/.thumbs');
					$thumb = $our_folder.'/.thumbs/100x100_'.$name.'.'.$ext;
					
					//$image = new files('tinyimages');
					//$this->Resize($source,$thumb,200,200,'back-ffffff');
					/******************************************************************
					*****************************************************************
					**************************************************************
					*******************************************************************/
					
/*>>>>>>>>>>>>>>*/	//Resize($source,$thumb,$ioptions["w"],$ioptions["h"],'thumb');//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<


					$result['result'] = 'success';
					$newadress="/storage";
					$newfilename= $name.'.'.$ext;
					if(isset ($_GET['uri']))$newadress=$_GET['uri'];
					if($newadress=="")$newadress="/storage";
						$result['filename'] = $newadress."/".$newfilename;
					if($_GET['lng']=='ru') {
						$result['size'] = "Загружено изображение ({$size['mime']}) размером {$size[0]}px/{$size[1]}px.";
					} else {
						$result['size'] = "Uploaded image ({$size['mime']}) size {$size[0]}px/{$size[1]}px.";
					}
				}
			}
		}
		else {
			$result['result'] = 'error';
			if($_GET['lng']=='ru') {
				$result['error'] = 'Нет файла или внутренняя ошибка!';
			} else {
				$result['error'] = 'No file or an internal error!';
			}
		}
		/*if (!headers_sent() ) {
			header('Content-type: application/json');
		}*/

		foreach ($result as $key=>$val) {
			$return[$key] = iconv("windows-1251", "utf-8", $val);
		}
		
		/*
		header("Content-Type: text/plain; charset=UTF-8");
		echo json_encode($return);
		exit();
		*/
		//var_dump($return);
		print '<script>';
	//	print "	alert('<img src=\"".$return['filename']."\" />');";

		print "window.parent.tinyMCE.getInstanceById(document.location.search.substr(1)).execCommand('mceInsertContent',false,'<img src=\"".$return['filename']."\" alt=\"\" />');\n\n";
//print "document.location.hash = '#".$_POST["inst"]."'";
		print '</script>';
	}
	if (isset($_FILES['image']) || !empty($_POST['base64'])) {
	UploadFiles();
	
	
	}else{
	
	}
	
	?>
    <div id="wrapper" style="position:absolute;left:0px;top:0px;">
	<form id="form1"  enctype="multipart/form-data" method="POST" action="" >
        <input id="File1" style="position:absolute;left:0px;top:0px;"  name="image" type="file" /> 
		 <input id="inst" name="inst"  type="hidden" /> 
		 <input  name="base64" id="base64" value=""  type="hidden" /> 
		</form>
    </div>
    <script type="text/javascript">
       
        var fileInput = document.getElementById('File1');
        var fileName = document.createElement('div');
        fileName.style.display = 'none';
        fileName.style.background = 'url(images/icons.png)';
        var activeButton = document.createElement('div');
        var bb = document.createElement('div');
        var bl = document.createElement('div');
        function WindowOnLoad()
        {
            var wrap = document.getElementById('wrapper');
            fileName.setAttribute('id','FileName');
            activeButton.setAttribute('id','activeBrowseButton');
            fileInput.value = '';
            fileInput.onchange = HandleChanges;
            fileInput.onmouseover = MakeActive;
            fileInput.onmouseout = UnMakeActive;
            fileInput.className = 'customFile';
            bl.className = 'blocker';
            bb.className = 'fakeButton';
            activeButton.className = 'fakeButton';
            wrap.appendChild(bb);
            wrap.appendChild(bl);
            
            wrap.appendChild(activeButton);
            
            wrap.appendChild(fileName);
           
            
        };
        function HandleChanges()
        {
            file = fileInput.value;
            reWin = /.*\\(.*)/;
            var fileTitle = file.replace(reWin, "$1");
            reUnix = /.*\/(.*)/;
            fileTitle = fileTitle.replace(reUnix, "$1");
            fileName.innerHTML = fileTitle;
            
            var RegExExt =/.*\.(.*)/;
            var ext = fileTitle.replace(RegExExt, "$1");
            
            var pos;
            if (ext){

                fileName.style.display = 'block';
	
				document.getElementById("inst").value=document.location.search.substr(1)
				
	
				document.getElementById("form1").submit();        
            };
            
        };
        function MakeActive()
        {
		
          activeButton.style.display = 'block';
        };
        function UnMakeActive()
        {
            activeButton.style.display = 'none';
        };
		WindowOnLoad()
    </script>
</body>
</html>
