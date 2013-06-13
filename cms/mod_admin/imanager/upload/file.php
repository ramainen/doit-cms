<?php
error_reporting(0);
header("Content-Type: text/plain; charset=utf-8");
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
			if (!preg_match('/\.(mp3|zip|flv|doc|rtf|swf|docx|xlsx|ies|pdf|zip|rar|xls|jpg|gif|png|jpeg|pptx?)$/i', $_FILES['Filedata']['name']) ) {
				print "ERROR_Invalid_filetype";
                                exit();
			} else  {
				$ext = substr($_FILES['Filedata']['name'],strrpos($_FILES['Filedata']['name'],'.')+1);
				$name = md5_file($_FILES['Filedata']['tmp_name']);
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
