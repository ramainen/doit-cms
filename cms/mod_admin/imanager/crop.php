<?php
include ('../../config.php');
include ('../../index.php');


session_start();
if(false==(session_is_registered("isadmin"))) {
    session_register("isadmin");
    $_SESSION["isadmin"]=0;

}
if ($_SESSION["isadmin"]!=1 )$_SESSION["isadmin"]=0;
$isadmin=$_SESSION["isadmin"];
if($isadmin!=1) 
 {
 
 exit();
 
 }
//header("Content-type: image/jpeg");
	// f - имя файла  
// type - способ масштабирования  
// q - качество сжатия  
// src - исходное изображение  
// dest - результирующее изображение  
// w - ширниа изображения  
// ratio - коэффициент пропорциональности  
// str - текстовая строка  
 
// тип преобразования, если не указаны размеры  

$f=$_POST["filename"];//"/storage/0b3e6bb4e833f345a14ebf255411a113.jpg";

if( substr($f,0,3)=="htt")
	
{
	$f=substr($f,7);
$ff = 	strpos($f,"/");
	$f=substr($f,$ff);
}

$ff=folderview($f);
$f ="../../".$f;

$format = strtolower(substr(strrchr($f,"."),1));
		switch($format)
		{
			case 'gif' :
				$type ="gif";
				$img = ImageCreateFromGif($f);
				break;
			case 'png' :
				$type ="png";
				$img = ImageCreateFromPng($f);
				break;
			case 'jpg' :
				$type ="jpg";
				$img = ImageCreateFromJpeg($f);
				break;
			case 'jpeg' :
				$type ="jpg";
				$img = ImageCreateFromJpeg($f);
				break;
			default :
				return false;
				break;
		}
$c_x  = $_POST["c_x"];
$c_y  = $_POST["c_y"];
$c_w  = $_POST["c_w"];
$c_h  = $_POST["c_h"];
 $h = $_POST["c_he"];//результат
 $w = $_POST["c_wi"];

  $dest = imagecreatetruecolor($w,$h);  
   imagecopyresized($dest, $img, 0, 0, $c_x,$c_y,   $w, $h, $c_w, $c_h);  

  //  imagejpeg($dest,'',100);  
   
$tofile = cpreview($f);
 //$tofile="";
	if($type=="gif")
		{
			imagegif($dest, $tofile);
		}
		elseif($type=="jpg")
		{
			imagejpeg($dest,$tofile, 100);
		}
		elseif($type=="png")
		{
			imagepng($dest, $tofile);
		}
		elseif($type=="bmp")
		{
			imagewbmp($dest, $tofile);
		}

  imagedestroy($dest);  
    imagedestroy($img); 

function cpreview($adress)
{
return	substr($adress,0,strrpos($adress, "/")+1).".thumbs/preview1_".substr($adress,strrpos($adress, "/")+1);
	
}
function folderview($adress)
{
	return	substr($adress,0,strrpos($adress, "/")+1);

}
//images.htm?uri=folderview($f)
//header ("Location: /i/imanager/images.htm?uri=".$ff);

?>
<script>
parent.close();
</script>