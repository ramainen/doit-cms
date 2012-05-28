<?php
 /**
 * Based on Images Manager server code by Antonov Andrey, dustweb.su
 */
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

		if (isset($_FILES['image'])) {
			$file = $_FILES['image']['tmp_name'];
			$error = false;
			$size = false;

			if (!is_uploaded_file($file) || ($_FILES['image']['size'] > 2 * 1024 * 1024) ) {
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
				$ext = substr($_FILES['image']['name'],strrpos($_FILES['image']['name'],'.')+1);
				$name = md5_file($_FILES['image']['tmp_name']);
				$source = $our_folder.'/'.$name.'.'.$ext;
				
				if(!copy($_FILES['image']['tmp_name'], $source)) {
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

		print "window.parent.tinyMCE.getInstanceById(document.location.search.substr(1)).execCommand('mceInsertContent',false,'<img src=\"".$return['filename']."\" />');\n\n";
//print "document.location.hash = '#".$_POST["inst"]."'";
		print '</script>';
	}
	if (isset($_FILES['image'])) {
	UploadFiles();
	
	
	}
	
	?>
    <div id="wrapper" style="position:absolute;left:0px;top:0px;">
	<form id="form1"  enctype="multipart/form-data" method="POST" action="" >
        <input id="File1" style="position:absolute;left:0px;top:0px;"  name="image" type="file" /> 
		 <input id="inst" name="inst"  type="hidden" /> 
		
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
