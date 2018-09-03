<?php

d()->route('/admin/tinymce-file-upload', function(){
	if (isset($_FILES['image']) || !empty($_POST['base64'])) {
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
			if (!$is_uploaded  ) {
				$error = 'Размер файла слишком велик';
			} else if (!$error && !($size = getimagesize($file) ) ) {
				$error = 'Ошибка, неверный тип файла';
			} else if (!$error && !in_array($size[2], array(1, 2, 3, 7, 8) ) ) {
				$error = 'Ошибка типа файла, рекомендуется загружать файлы JPEG';
			} else if (!$error && ($size[0] < 1) || ($size[1] < 1)) {
				$error = 'Размер файла слишком маленький';
			}
			if ($error) {
				$result['result'] = 'failed';
				$result['error'] = $error;
			} else {
				$ext = mb_strtolower(end(explode(".", $filename)), 'UTF-8' );
				$name = tinymce_file_upload_findname($file,$our_folder,$filename,$is_magic);
				$source = $our_folder.'/'.$name.'.'.$ext;

				if(!copy($file, $source)) {
					$result['result'] = 'error';
					$result['error'] = 'Ошибка при копировании файла!';
				} else {
					$result['result'] = 'success';
					$newadress="/storage";
					$newfilename= $name.'.'.$ext;
					if(isset ($_GET['uri']))$newadress=$_GET['uri'];
					if($newadress=="") {
						$newadress="/storage";
					}
					$result['filename'] = $newadress."/".$newfilename;
					$result['size'] = "Загружено изображение ({$size['mime']}) размером {$size[0]}px/{$size[1]}px.";
				}
			}
		} else {
			$result['result'] = 'error';
			if($_GET['lng']=='ru') {
				$result['error'] = 'Нет файла или внутренняя ошибка!';
			} else {
				$result['error'] = 'No file or an internal error!';
			}
		}
		
		$return=array();
		foreach ($result as $key=>$val) {
			$return[$key] =   $val ;
		}
		print '<script>';
		print "window.parent.tmp_tinymce_editor.insertContent('<img src=\"". htmlspecialchars($return['filename']) ."\" alt=\"\" />');";
		print '</script>';
	
	} 
	exit;
	
});