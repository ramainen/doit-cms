<?php
error_reporting(0);
header("Content-Type: text/plain; charset=utf-8");
class watermark3{

	# given two images, return a blended watermarked image
	function create_watermark( $main_img_obj, $watermark_img_obj, $alpha_level = 100 ) {
		$alpha_level	/= 100;	# convert 0-100 (%) alpha to decimal

		# calculate our images dimensions
		$main_img_obj_w	= imagesx( $main_img_obj );
		$main_img_obj_h	= imagesy( $main_img_obj );
		$watermark_img_obj_w	= imagesx( $watermark_img_obj );
		$watermark_img_obj_h	= imagesy( $watermark_img_obj );

		# determine center position coordinates
		$main_img_obj_min_x	= floor( ( $main_img_obj_w / 2 ) - ( $watermark_img_obj_w / 2 ) );
		$main_img_obj_max_x	= ceil( ( $main_img_obj_w / 2 ) + ( $watermark_img_obj_w / 2 ) );
		$main_img_obj_min_y	= floor( ( $main_img_obj_h / 2 ) - ( $watermark_img_obj_h / 2 ) );
		$main_img_obj_max_y	= ceil( ( $main_img_obj_h / 2 ) + ( $watermark_img_obj_h / 2 ) ); 

		# create new image to hold merged changes
		$return_img	= imagecreatetruecolor( $main_img_obj_w, $main_img_obj_h );

		# walk through main image
		for( $y = 0; $y < $main_img_obj_h; $y++ ) {
			for( $x = 0; $x < $main_img_obj_w; $x++ ) {
				$return_color	= NULL;

				# determine the correct pixel location within our watermark
				$watermark_x	= $x - $main_img_obj_min_x;
				$watermark_y	= $y - $main_img_obj_min_y;

				# fetch color information for both of our images
				$main_rgb = imagecolorsforindex( $main_img_obj, imagecolorat( $main_img_obj, $x, $y ) );

				# if our watermark has a non-transparent value at this pixel intersection
				# and we're still within the bounds of the watermark image
				if (	$watermark_x >= 0 && $watermark_x < $watermark_img_obj_w &&
							$watermark_y >= 0 && $watermark_y < $watermark_img_obj_h ) {
					$watermark_rbg = imagecolorsforindex( $watermark_img_obj, imagecolorat( $watermark_img_obj, $watermark_x, $watermark_y ) );

					# using image alpha, and user specified alpha, calculate average
					$watermark_alpha	= round( ( ( 127 - $watermark_rbg['alpha'] ) / 127 ), 2 );
					$watermark_alpha	= $watermark_alpha * $alpha_level;

					# calculate the color 'average' between the two - taking into account the specified alpha level
					$avg_red		= $this->_get_ave_color( $main_rgb['red'],		$watermark_rbg['red'],		$watermark_alpha );
					$avg_green	= $this->_get_ave_color( $main_rgb['green'],	$watermark_rbg['green'],	$watermark_alpha );
					$avg_blue		= $this->_get_ave_color( $main_rgb['blue'],	$watermark_rbg['blue'],		$watermark_alpha );

					# calculate a color index value using the average RGB values we've determined
					$return_color	= $this->_get_image_color( $return_img, $avg_red, $avg_green, $avg_blue );

				# if we're not dealing with an average color here, then let's just copy over the main color
				} else {
					$return_color	= imagecolorat( $main_img_obj, $x, $y );

				} # END if watermark

				# draw the appropriate color onto the return image
				imagesetpixel( $return_img, $x, $y, $return_color );

			} # END for each X pixel
		} # END for each Y pixel

		# return the resulting, watermarked image for display
		return $return_img;

	} # END create_watermark()

	# average two colors given an alpha
	function _get_ave_color( $color_a, $color_b, $alpha_level ) {
		return round( ( ( $color_a * ( 1 - $alpha_level ) ) + ( $color_b	* $alpha_level ) ) );
	} # END _get_ave_color()

	# return closest pallette-color match for RGB values
	function _get_image_color($im, $r, $g, $b) {
		$c=imagecolorexact($im, $r, $g, $b);
		if ($c!=-1) return $c;
		$c=imagecolorallocate($im, $r, $g, $b);
		if ($c!=-1) return $c;
		return imagecolorclosest($im, $r, $g, $b);
	} # EBD _get_image_color()

} # END watermark API


class tinyimages {
 
        var $filesendname='Filedata';
	var $folder = '/storage';

	function __construct() {
		define(DIR, $_SERVER['DOCUMENT_ROOT'].'');

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
			if ( !($size = @getimagesize($file) ) ) {
					print "error2";
                                exit();
			} else
			if (!$error && !in_array($size[2], array(1, 2, 3, 7, 8) ) ) {
				print "error3";
                                exit();
			} else  {
				$ext = substr($_FILES['Filedata']['name'],strrpos($_FILES['Filedata']['name'],'.')+1);
				$name = md5_file($_FILES['Filedata']['tmp_name']);
				if(!file_exists(DIR.$this->folder.'/.thumbs')) mkdir(DIR.$this->folder.'/.thumbs');
					if($ioptions["water"]!=''){
					$name=$name."_water";
					
					}
				$source = DIR.$this->folder.'/'.$name.'.'.$ext;

				if(! @copy($_FILES['Filedata']['tmp_name'], $source)) {
					//print "error4";
				}
				if(true) {

					if(!file_exists(DIR.$this->folder.'/.thumbs')) mkdir(DIR.$this->folder.'/.thumbs');
					if($ioptions["water"]!=''){
					 
						$thumb = DIR.$this->folder.'/.thumbs/preview1_'.$name.'.'."png";
					}else{
						$thumb = DIR.$this->folder.'/.thumbs/preview1_'.$name.'.'.$ext;
					}
					//$thumb2 = DIR.$this->folder.'/.thumbs/preview_2_'.$name.'.'.$ext;

					//$image = new files('tinyimages');
					//$this->Resize($source,$thumb,200,200,'back-ffffff');
					/******************************************************************
					 *****************************************************************
					 **************************************************************
					 *******************************************************************/
					
/*>>>>>>>>>>>>>>*/	$this->Resize($source,$thumb,$ioptions["w"],$ioptions["h"],'thumb',$ioptions["water"]);//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*>>>>>>>>>>>>>>*/
					$i=2;
					$fl=0;
					while($fl==0)
					{
					if(isset($ioptions["w".$i]) && isset($ioptions["h".$i])) {
					$thumb = DIR.$this->folder.'/.thumbs/preview'.$i.'_'.$name.'.'.$ext;
						$this->Resize($source,$thumb,$ioptions["w".$i],$ioptions["h".$i],'thumb');
						$i++;
					}else
					{
					$fl=1;
					}
					}

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
 


	function Resize($filename, $dest, $width, $height, $pictype = "",$water='') {
		$format = strtolower(substr(strrchr($filename,"."),1));
		switch($format)
		{
			case 'gif' :
				$type ="gif";
				$img = ImageCreateFromGif($filename);
				break;
			case 'png' :
				$type ="png";
				$img = ImageCreateFromPng($filename);
				imageSaveAlpha($img, true);
				break;
			case 'jpg' :
				$type ="jpg";
				$img = ImageCreateFromJpeg($filename);
				break;
			case 'jpeg' :
				$type ="jpg";
				$img = ImageCreateFromJpeg($filename);
				break;
			default :
				return false;
				break;
		}

		list($org_width, $org_height) = getimagesize($filename);
		$xoffset = 0;
		$yoffset = 0;
		
		if ($pictype == "thumb") // To minimize destortion
		{

		if($height=='auto'){
		$height=round($width* ($org_height/$org_width));
		}else{
			if($width=='auto'){
				$width=round($height* ($org_width/$org_height));
			}
		}
		if ($width / $height <   $org_width / $org_height)
			{
			$dy=0;
			$xtmp = $org_width;
			$org_width= ($width*$org_height)/$height;

			$dx = 0.5*(  	$xtmp - $org_width);
			$xoffset=$dx;
			$yoffset=$dy;

			}
			else
			{
				$dx=0;
			$ytmp = $org_height;
			$org_height= ($height*$org_width)/$width;

			$dy = 0.5*(  	$ytmp - $org_height);
			$xoffset=$dx;
			$yoffset=$dy;
			}
			//Added this else part -------------
		} elseif(substr($pictype,0,4) == "back") {
			$xtmp = $org_width/$width;
			$new_width = $width;
			$new_height = $org_height/$xtmp;
			if ($new_height > $org_height && $new_width > $org_width) {
				$new_height = $org_height;
				$new_width = $org_width;
			} elseif ($new_height > $height){
				$ytmp = $org_height/$height;
				$new_height = $height;
				$new_width = $org_width/$ytmp;
			}
			$width_d = round($new_width)<$width?$width:round($new_width);
			$height_d = round($new_height)<$height?$height:round($new_height);

			$width = round($new_width);
			$height = round($new_height);

			$width_diff = $width_d - $width;
			$height_diff = $height_d - $height;
		} else {
			$xtmp = $org_width/$width;
			$new_width = $width;
			$new_height = $org_height/$xtmp;
			if ($new_height > $height){
				$ytmp = $org_height/$height;
				$new_height = $height;
				$new_width = $org_width/$ytmp;
			}
			$width = round($new_width);
			$height = round($new_height);
		}

		if(substr($pictype,0,4) == "back") {
			$img_n=imagecreatetruecolor ($width+$width_diff, $height+$height_diff);
			imagecolortransparent($img_n, $black);
			$r = hexdec(substr($pictype,5,2));
			$g = hexdec(substr($pictype,7,2));
			$b = hexdec(substr($pictype,9,2));
			$back = imagecolorallocate($img_n, $r, $g, $b);
			imagefill($img_n, 0, 0, $back); //imageSaveAlpha($img_n, true);
			imagecopyresampled($img_n, $img, round($width_diff/2), round($height_diff/2), $xoffset, $yoffset, $width, $height, $org_width, $org_height);
		} else {

			$img_n=imagecreatetruecolor ($width, $height);
		 if($water!=''){
		 }else{
			imagealphablending($img_n, false);
		 }
			 imagesavealpha($img_n, true);
			$black = imagecolorallocate($img_n, 0, 0, 0);
			$black2 = imagecolorallocate($img, 0, 0, 0);
			imageSaveAlpha($img, true);
			//	imagecolortransparent($img, $black2);
			//	imagecolortransparent($img_n, $black);
			
			imagecopyresampled($img_n, $img, 0, 0, $xoffset, $yoffset, $width, $height, $org_width, $org_height);
			
			if($water!=''){

					$imgwater = ImageCreateFromPng($_SERVER['DOCUMENT_ROOT'].'/'.$water);
					imagealphablending($imgwater, false);
					imageSaveAlpha($imgwater, true);
					imagealphablending($img_n, false);
					imageSaveAlpha($img_n, true);
					//	imagecopyresampled($img_n, $imgwater, 0, 0,0, 0,111, 111, 111,111);
					//		imagecopymerge ( $img_n, $imgwater,0, 0,0,0,66,66,55);
					$watermark = new watermark3();

					$img_n=$watermark->create_watermark($img_n,$imgwater,100);

			}
		//	imagecolortransparent($img_n, $black);

			
			
			
		}
		if($water!=''){
	imagepng($img_n, $dest);
	
	}else{
		if($type=="gif")
		{
			imagegif($img_n, $dest);
		}
		elseif($type=="jpg")
		{
			imagejpeg($img_n, $dest, 100);
		}
		elseif($type=="png")
		{


			imagepng($img_n, $dest);
		}
		elseif($type=="bmp")
		{
			imagewbmp($img_n, $dest);
		}}
		return true;
	}
}

$ioptions["h"]=100;
$ioptions["w"]=150;
$ioptions["water"]='';
 
if(isset($_GET["w"]))$ioptions["w"]=$_GET["w"];
if(isset($_GET["h"]))$ioptions["h"]=$_GET["h"];
if(isset($_GET["water"]))$ioptions["water"]=$_GET["water"]; 
$i=2;
$fl=0;
while($fl==0)
{
if(isset($_GET["w".$i]) && isset($_GET["h".$i])) {
	$ioptions["w".$i]=$_GET["w".$i];
	$ioptions["h".$i]=$_GET["h".$i];
	$i++;
}else
{
$fl=1;
}
}
$images = new tinyimages();
$images->UploadFiles();
