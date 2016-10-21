<?php

//helper input для отображения текстового поля
function input ($params=array())
{
	if(!isset($params['type'])) {
		$params['type'] = 'text';
	}
	print '<input ' . inputparams($params) . ' >';
}

function inputparams($params=array())
{

	$cfo = d()->current_form_object;
	$attr='';
	if(isset($params['style'])) {
		$attr .= ' style="'.$params['style'].'" ';
	}
	
	//TODO: проверка на класс error
	if(isset($params['class'])) {
		$attr .= ' class="'.$params['class'].'" ';
	}
	
	if(isset($params['type'])) {
		$attr .= ' type="'.$params['type'].'" ';
	}
	
	if(isset($params['title'])) {
		$attr .= ' title="'.$params['title'].'" ';
	}
	
	if(isset($params['placeholder'])) {
		$attr .= ' placeholder="'.$params['placeholder'].'" ';
	}
	
	if(isset($params['name'])) {
		$attr .= ' name="'.$params['name'].'" ';
		
	} else {
		if(d()->current_form_simple_names){
			$attr .= ' name="'.$params[0].'" ';
		}else{
			$attr .= ' name="'.$cfo.'['.$params[0].']" ';
		}
		
	}
//	print '<!-- ';
//	var_dump($_POST['document[document_type]']);
//	print ' -->';
	
	if(isset($params['checked'])) {
		$attr .= ' checked="'.$params['checked'].'" ';
	} else {
		
		if ($params['type']=='radio'){
			if(isset($_POST['_action'])) {
				//Был POST-запрос

				if(d()->current_form_simple_names){
					if(isset($params['name'])){

						if( isset($params['value']) && $_POST[$params['name']]==$params['value']) {
							//Совпало значение
							$attr .= ' checked="checked" ';
						}
					}else{
						
						if( isset($params['value']) && $_POST[$params[0]]==$params['value']) {
							//Совпало значение
							$attr .= ' checked="checked" ';
						}
					}
				}else{
					if(isset($params['name'])){

						if( isset($params['value']) && $_POST[$params['name']]==$params['value']) {
							//Совпало значение
							$attr .= ' checked="checked" ';
						}
					}else{
						
						if( isset($params['value']) && $_POST[$cfo][$params[0]]==$params['value']) {
							//Совпало значение
							$attr .= ' checked="checked" ';
						}
					}
				}
			} else {
				if(isset($params['value']) && count(d()->{$cfo})>0 && d()->{$cfo}->{$params[0]}==$params['value']){
					$attr .= ' checked="checked" ';
				}else{
					if(isset($params['value']) && (count(d()->{$cfo})==0 || d()->{$cfo}->{$params[0]}=='') && isset($params['default_checked'])){
						$attr .= ' checked="checked" ';	
					
					}
				}

			}
		}
	}
	
	if(isset($params['id'])) {
		$attr .= ' id="'.$params['id'].'" ';
	}else{
		$attr .= ' id="'.$cfo.'_'.$params[0].'" ';		
	}
	
	if(isset($params['attr'])) {
		$attr .= ' '.$params['attr'].' ';
	}
	
	if(isset($params['value'])) {
		$attr .= ' value="'.$params['value'].'" ';
	}else{
		if(isset($_POST['_action'])) {				
			if(isset($params['name'])) {
				$attr .= ' value= "'.  htmlspecialchars( $_POST[$params['name']]) .'" ';
			} else {
				if(d()->current_form_simple_names){
					$attr .= ' value= "'.  htmlspecialchars($_POST[$params[0]]) .'" ';
				}else{
					$attr .= ' value= "'.  htmlspecialchars($_POST[$cfo][$params[0]]) .'" ';
				}
			}
		} else {
			$attr .= ' value= "'.  htmlspecialchars(d()->{$cfo}->{$params[0]}) .'" ';
		}
	}
	
	return ' ' . $attr . ' ';
}


//helper textarea для отображения текстового поля
function textarea ($params=array())
{

	$cfo = d()->current_form_object;
	$attr='';
	if(isset($params['style'])) {
		$attr .= ' style="'.$params['style'].'" ';
	}
	
	//TODO: проверка на класс error
	if(isset($params['class'])) {
		$attr .= ' class="'.$params['class'].'" ';
	}

	if(isset($params['placeholder'])) {
		$attr .= ' placeholder="'.$params['placeholder'].'" ';
	}

	if(isset($params['rows'])) {
		$attr .= ' rows="'.$params['rows'].'" ';
	}
	
	if(isset($params['id'])) {
		$attr .= ' id="'.$params['id'].'" ';
	}else{
		$attr .= ' id="'.$cfo.'_'.$params[0].'" ';		
	}
	
	if(isset($params['attr'])) {
		$attr .= ' '.$params['attr'].' ';
	}
	
	
	if(isset($params['name'])) {
		$attr .= ' name="'.$params['name'].'" ';
		
	} else {
		if(d()->current_form_simple_names){
			$attr .= ' name="'.$params[0].'" ';
		}else{
			$attr .= ' name="'.$cfo.'['.$params[0].']" ';
		}
		
	}
	
	$value="";
	if(isset($params['value'])) {
		$value = htmlspecialchars( $params['value']);
	}else{
		if(isset($_POST['_action'])) {					
			if(isset($params['name'])) {
				$value =  htmlspecialchars( $_POST[$params['name']]) ;
			} else {
				if(d()->current_form_simple_names){
					$value =   htmlspecialchars($_POST[$params[0]]) ;
				}else{
					$value =   htmlspecialchars($_POST[$cfo][$params[0]]) ;
				}
			}
		} else {
			$value =  htmlspecialchars(d()->{$cfo}->{$params[0]}) ;
		}
	}
	
	return '<textarea ' . $attr . '>' . $value . '</textarea>';
}

function form ($params=array()) 
{
	$attr="";
	$additions = '';
	d()->current_form_simple_names = false;
	if (isset($params[1])) {
		d()->current_form_object = $params[1];
	} else {
		d()->current_form_object = 'data';
	}
	
	if(isset($params['action'])) {
		$attr .= ' action="'.$params['action'].'" ';
	}
	
	if(isset($params['ajax']) && $params['ajax']==true) {
		$attr .= ' onsubmit="window._current_form=$(this);$.ajax({\'type\':\'post\',\'url\': $(this).attr(\'action\')?$(this).attr(\'action\'):document.location.href ,\'data\':$(this).serialize(),\'success\':function(recieved_data){eval(recieved_data)}});return false;" ';
		
	}
	
	if(isset($params['simple_names']) && $params['simple_names']==true) {
		d()->current_form_simple_names = true;
		$additions .= ' <input type="hidden" name="_is_simple_names" value="1" >';
	}	
	if(isset($params['iframe']) && $params['iframe']==true) {
		$iframe_id = "hidden_".rand(111,999);
		$attr .= ' target="'.$iframe_id.'" ';
		
		$additions .= '<iframe style="display:none" id="'.$iframe_id.'" name="'.$iframe_id.'"></iframe>';
		$additions .= ' <input type="hidden" name="_is_iframe" value="1" >';
		
	}
		
	if(isset($params['global']) && $params['global']==true) {
		
		$additions .= ' <input type="hidden" name="_global" value="1" >';
		if(!isset($_SESSION['_form_sign_key']) || $_SESSION['_form_sign_key']==''){
			
			$key = sha1 (uniqid().mt_rand().microtime().'salt'.$_SERVER["REMOTE_PORT"].mt_rand());
			$_SESSION['_form_sign_key'] = $key;
			
			 
		}else{
			$key = $_SESSION['_form_sign_key'];
		}
		$run_before='';
		if(isset($params['run_before']) && $params['run_before']!='') {
			$run_before = md5($params['run_before']);
		}
		
		$sign = sha1('salt_sign'.md5($key).md5(d()->current_form_object).md5($params[0]).$run_before);
		$additions .= ' <input type="hidden" name="_run_before" value="'.$params['run_before'].'" >';
		$additions .= ' <input type="hidden" name="_global_sign" value="'.$sign.'" >';
		
	}
	
	if(isset($params['style'])) {
		$attr .= ' style="'.$params['style'].'" ';
	}
	
	if(isset($params['target'])) {
		$attr .= ' target="'.$params['target'].'" ';
	}
	
	if(isset($params['class'])) {
		$attr .= ' class="'.$params['class'].'" ';
	}
	
	if(isset($params['enctype'])) {
		$attr .= ' enctype="'.$params['enctype'].'" ';
	}else{
		if(isset($params['upload']) && $params['upload']==true) {
			$attr .= ' enctype="multipart/form-data" ';
		}
	}
	if(isset($params['id'])) {
		$attr .= ' id="'.$params['id'].'" ';
	}	
	
	
	$result =  "<form method='POST' ".$attr.">";
	$result .= ' <input type="hidden" name="_element" value="' . d()->current_form_object .'" >';
	$result .= ' <input type="hidden" name="_action" value="'.$params[0].'" >';
	$result .= $additions;
	return $result;
	
}


function tag ($params=array())
{

	$attr='';

	if(isset($params['attr'])) {
		$attr .= ' '.$params['attr'].' ';
	}
	
	foreach($params as $key=>$value){
		if(!is_numeric($key) && ($key!='attr')){
			$attr .= ' '.$key.'="'.$value.'" ';
		}
	}
	
	if(isset($params[1])) {
		return '<'.$params[0].  $attr . '>'.$params[1].'</'.$params[0].'>';
	}else{
		return '<'.$params[0].  $attr . '>';
	}
	
}

function notice($params=array())
{
	//Если пусто, не выводить
	if(d()->notice=='' || count(d()->notice)==0) {
		return '';
	}
	$str='';
	
	if($params[0]=='bootstrap'){
		//Пользуемся оттсестированной функцией tag()
		if(!isset($params['style'])){
			$params['style']='';
			$params['class']='alert alert-error';
		}
		$params[0]='ul';
		
		$str .= tag($params)	;
		
		foreach(d()->notice as $value){
		$str .='<li style="margin-left:20px;">'.$value.'</li>';
	}
	
	}else{
		//Пользуемся оттсестированной функцией tag()
		if(!isset($params['style'])){
			$params['style']='padding:15px;padding-left:25px;border:1px solid red;';
		}
		$params[0]='ul';
		
		$str .= tag($params);
		
		foreach(d()->notice as $value){
		$str .='<li>'.$value.'</li>';
	}
	
	
	}
	
	
	
	
	
	$str .= '</ul>';


	return $str;
}

function jquery_notice(){
	if(isset(d()->datapool['inputs_with_errors']) && count(d()->datapool['inputs_with_errors'])!=0 && isset($_POST['_element'])){
	$noticed_inputs = array_values(d()->datapool['inputs_with_errors']);
	$response.=  "$('.error').removeClass('error');\n";
	foreach($noticed_inputs as $key=>$input){
		if(isset($_POST['_is_simple_names']) && $_POST['_is_simple_names']=='1'){
			$element_name = "'*[name=\"".$input."\"]'";
		}else{
			$element_name = "'*[name=\"".$_POST['_element'].'['.$input.']'."\"]'";	
		}
		
		$response .=  '$('.$element_name.').parent().parent().addClass("error");'."\n";
	}
	print "\n<script>".'$(function(){'.$response.'});'."</script>\n";
}
}

function flash($params=array())
{
	
	//Если пусто, не выводить
	if(!isset($_SESSION['flash']) || $_SESSION['flash']=='' ){
		return '';
	}
	$str='';
	
	if($params[0]=='bootstrap'){
		//Пользуемся оттсестированной функцией tag()
		if(!isset($params['style'])){
			$params['style']='';
			$params['class']='alert alert-info';
		}
		
		$params[0]='div';
		$str .= tag($params);
		$str .=$_SESSION['flash'];
	} else{
		$params[0]='div';
		$str .= tag($params);
		if(!isset($params['style'])){
			$params['style']='';
		}
		$str .=$_SESSION['flash'];
	}
	$str .= '</div>';
	unset($_SESSION['flash']);
	return $str;
}

function link_to($params)
{
	
	$href = path_to(array($params[0]));
	
	$attr='';
	
	if(isset($params['style'])) {
		$attr .= ' style="'.$params['style'].'" ';
	}
	
	//TODO: проверка на класс error
	if(isset($params['class'])) {
		$attr .= ' class="'.$params['class'].'" ';
	}
	
	if(isset($params['id'])) {
		$attr .= ' id="'.$params['id'].'" ';
	}
	
	if(isset($params['attr'])) {
		$attr .= ' '.$params['attr'].' ';
	}
	
	return '<a href= "' . $href . '" ' . $attr . '>'.$params[1].'</a>';
	
}



//DEPRECATED
function path_to($params)
{
	$result='/';
	foreach (d()->urls as $rules) {
		foreach($rules as $rule) {
			if ($rule == $params[0]) {
				$result = $rules[0];
			}
		}
	}
	if(substr($result,-6)=='/index') {
		$result = substr($result,0,-5);
	}
	return $result;
	
}

function preview($adress,$param1=false,$param2=false )
{	
	$orig_params = $adress;
	if(is_array($adress)){
		if(isset($adress['height']) || isset($adress[2])){
			if(isset($adress['width'])){
				$width=$adress['width'];
			}else{
				$width=$adress[1];
			}
			if(isset($adress['height'])){
				$height=$adress['height'];
			}else{
				$height=$adress[2];
			}
			$adress = $adress[0];
		} else {
			$num=1;
			if(isset($adress[1])){
				$num=$adress[1];
			}
			$adress = $adress[0];
			if($adress==''){
				return '';
			}
			$ext=strtolower(strrchr($adress, '.'));
			if(!in_array($ext,array('.gif','.jpg','.png','.jpeg'))){
				return '';
			}
			return substr($adress, 0, strrpos($adress, "/") + 1) . ".thumbs/preview".$num."_" . substr($adress, strrpos($adress, "/") + 1);
		}
		//Массив значений
	}else{
		if($param2===false){
			//обычная превью
			$num=1;
			if($param1!==false){
				$num=$param1;
			}
			if($adress==''){
				return '';
			}
			$ext=strtolower(strrchr($adress, '.'));
			if(!in_array($ext,array('.gif','.jpg','.png','.jpeg'))){
				return '';
			}
			return substr($adress, 0, strrpos($adress, "/") + 1) . ".thumbs/preview".$num."_" . substr($adress, strrpos($adress, "/") + 1);
		}else{
			//необычная превью без массива значений
			$width=$param1;
			$height=$param2;
		}
	}
	if($adress==''){
		return '';
	}
	$ext=strtolower(strrchr($adress, '.'));
	if(!in_array($ext,array('.gif','.jpg','.png','.jpeg'))){
		return '';
	}
	$not_resize_hash = '';
	if($orig_params['not_resize']){
		$not_resize_hash = '_nrs_';
	}
	if(is_array($orig_params) && isset($orig_params['watermark'])){
		$is_watermark = true;
		$preview_adress = substr($adress, 0, strrpos($adress, "/") + 1) . ".thumbs/preview_watermark".$not_resize_hash. md5($orig_params['watermark'].$width.'x'.$height."_" . substr($adress, strrpos($adress, "/") + 1)) . $ext;
	}else{
		$is_watermark = false;
		$preview_adress = substr($adress, 0, strrpos($adress, "/") + 1) . ".thumbs/preview".$not_resize_hash.$watermark_suffix.$width.'x'.$height."_" . substr($adress, strrpos($adress, "/") + 1);
	}
	
	
	
	//генерирование изображения при его отсуствии
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].$preview_adress)){
		
		//Создание превью
		
		$filename = $_SERVER['DOCUMENT_ROOT'].$adress;
		if (!is_file($filename)) {
			return '';
		}
		$dest = $_SERVER['DOCUMENT_ROOT'].$preview_adress;
		
		$dest_folder = $_SERVER['DOCUMENT_ROOT'].substr($adress, 0, strrpos($adress, "/") + 1) . ".thumbs";
		if(!file_exists($dest_folder)){
			mkdir($dest_folder);
			chmod($dest_folder, 0777);
		}
		
		$format = strtolower(substr(strrchr($filename,"."),1));
		switch($format) {
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
		$nch_org_width = $org_width;
		$nch_org_height = $org_height;
		
		$xoffset = 0;
		$yoffset = 0;
	
		if (strpos($height, 'in') !== false AND strpos($width, 'in') !== false) {
			$height_temp = substr($height, 2);
			$width_temp = substr($width, 2);
			
			if($org_width<=$width_temp &&  $org_height<=$height_temp && $orig_params['not_resize']){
				$height= $org_height;
				$width= $org_width;
			}else {
				$h_index = ($org_height / $height_temp);
				$w_index = ($org_width / $width_temp);
				
				$index = $h_index;
				if ($h_index < $w_index) {
					$index = $w_index;
				}
				$width = round($org_width / $index);
				$height = round($org_height / $index);
			}
		} else {
			if (strpos($height, 'in' === 0)) {
				$height = substr($height, 2);
			} elseif (strpos($width, 'in' === 0)) {
				$width = substr($width, 2);
			}
		}
		if($height=='auto' && $width=='auto'){
			$height= $org_height;
			$width= $org_width;
		}else if($height=='auto'){
			$height=round($width* ($org_height/$org_width));
		}else{
			if($width=='auto'){
				$width=round($height* ($org_width/$org_height));
			}
		}
		if ($width / $height <   $org_width / $org_height) {
			$dy=0;
			$xtmp = $org_width;
			$org_width= ($width*$org_height)/$height;

			$dx = 0.5*(  	$xtmp - $org_width);
			$xoffset=$dx;
			$yoffset=$dy;
		} else {
			$dx=0;
			$ytmp = $org_height;
			$org_height= ($height*$org_width)/$width;

			$dy = 0.5*($ytmp - $org_height);
			$xoffset=$dx;
			$yoffset=$dy;
		}
		
		if($org_height <= $height &&  $org_width <= $width && $orig_params['not_resize']){
			$height= $nch_org_height;
			$width= $nch_org_width;
			$xoffset=0;
			$yoffset=0;
			$org_height= $nch_org_height;
			$org_width= $nch_org_width;
		}
		$img_n=imagecreatetruecolor ($width, $height);
		imagealphablending($img_n, false);
		imagesavealpha($img_n, true);
		$black = imagecolorallocate($img_n, 0, 0, 0);
		$black2 = imagecolorallocate($img, 0, 0, 0);
		imageSaveAlpha($img, true);
		
		imagecopyresampled($img_n, $img, 0, 0, $xoffset, $yoffset, $width, $height, $org_width, $org_height);
  
		/* watermark*/
		if($is_watermark){
			$imgwater = ImageCreateFromPng($_SERVER['DOCUMENT_ROOT']. $orig_params['watermark']  );
			imagealphablending($imgwater, false);
			imageSaveAlpha($imgwater, true);
			imagealphablending($img_n, false);
			imageSaveAlpha($img_n, true);
			$watermark = new Watermark_creator();

			$img_n=$watermark->create($img_n,$imgwater,100);
		}
		
		if($type=="gif") {
			imagegif($img_n, $dest);
		} elseif($type=="jpg") {
			imagejpeg($img_n, $dest, 100);
		} elseif($type=="png") {
			imagepng($img_n, $dest);
		} elseif($type=="bmp") {
			imagewbmp($img_n, $dest);
		}
		chmod($dest, 0777);
	}
	return $preview_adress;
}



function h($html)
{
	return htmlspecialchars($html);
}

function hnl2br($html)
{
	return nl2br(htmlspecialchars($html));
}

function et($string)
{
	$string=str_replace(array('"',"'",'\\',' ','.','*','/','`',')'),array('','','','','','','','',''),$string);
	return $string;
}
function e($string)
{
	return d()->db->quote($string);
}
//получение множественного числа
function to_o($plural)
{
	return ActiveRecord::plural_to_one($plural);
}
//Получение единственного числа
function to_p($one)
{
	return ActiveRecord::one_to_plural($one);
}

function to_camel($string)
{
 
	return strtoupper(substr($string,0,1)).substr($string,1);
}

function t($text)
{
	if(!empty(d()->translate[$text])){
		return d()->translate[$text];
	}
	return $text;
}


/**
 * Склонение числительных
 *
 * <code>
 * declOfNum(4, array('помидор', 'помидора', 'помидоров')
 * </code>
 *
 * @param $number
 * @param $titles
 * @return string
 */
function declOfNum($number, $words=false,$word2=false,$word3=false)
{
	if(is_array($number)){
		$arr = $number;
		$number = $arr[0];
		if(isset($arr[1])){
			$words = $arr[1];
		}
		if(isset($arr[2])){
			$word2 = $arr[2];
		}
		if(isset($arr[3])){
			$word3 = $arr[3];
		}


	}
	$words_result = $words;	
	$checks = array (2, 0, 1, 1, 1, 2);
	if(is_string($words) && is_string($word2) && is_string($word3)){
		$words_result=array($words,$word2,$word3);
	}
	if(is_string($words) && $word2==false){
		//Режим магии
		$word1 = $words;
		$word2=$words;
		$word3=$words; 
		if(preg_match('#[a-z]#i',$word1)){
			if($number==1){
				return $word1;
			}else{
				return to_p($word1);
			}
		}elseif(mb_substr($word1,-2,2,"UTF-8")=='ий'){
			$word2 = mb_substr($word1,0,-2,"UTF-8").'ия';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'иев';
		}elseif( $word1 =='человек'){
			$word2 = 'человека';
			$word3 = 'человек';
		}elseif( $word1 =='раз'){
			$word2 = 'раза';
			$word3 = 'раз';
		}elseif(mb_substr($word1,-4,4,"UTF-8")=='деец'){

			$word2 = mb_substr($word1,0,-4,"UTF-8").'дейца';
			$word3 = mb_substr($word1,0,-4,"UTF-8").'дейцев';

		}elseif(mb_substr($word1,-3,3,"UTF-8")=='еец'){

			$word2 = mb_substr($word1,0,-3,"UTF-8").'ейца';
			$word3 = mb_substr($word1,0,-3,"UTF-8").'йцев';

		}elseif(mb_substr($word1,-3,3,"UTF-8")=='аец'){

			$word2 = mb_substr($word1,0,-3,"UTF-8").'айца';
			$word3 = mb_substr($word1,0,-3,"UTF-8").'айцев';

		}elseif(mb_substr($word1,-4,4,"UTF-8")=='анец'){

			$word2 = mb_substr($word1,0,-4,"UTF-8").'анца';
			$word3 = mb_substr($word1,0,-4,"UTF-8").'анцев';

		}elseif(mb_substr($word1,-3,3,"UTF-8")=='нец'){

			$word2 = mb_substr($word1,0,-3,"UTF-8").'нца';
			$word3 = mb_substr($word1,0,-3,"UTF-8").'нцов';

		}elseif(mb_substr($word1,-3,3,"UTF-8")=='оец'){

			$word2 = mb_substr($word1,0,-3,"UTF-8").'ойца';
			$word3 = mb_substr($word1,0,-3,"UTF-8").'ойцов';

		}elseif(mb_substr($word1,-3,3,"UTF-8")=='дец'){

			$word2 = mb_substr($word1,0,-3,"UTF-8").'дца';
			$word3 = mb_substr($word1,0,-3,"UTF-8").'дцов';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='ец'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ца';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'цев';

		}elseif(mb_substr($word1,-1,1,"UTF-8")=='р'){

			$word2 = mb_substr($word1,0,-1,"UTF-8").'ра';
			$word3 = mb_substr($word1,0,-1,"UTF-8").'ров';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='во'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ва';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'в';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='ло'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ла';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'л';

		}elseif(mb_substr($word1,-1,1,"UTF-8")=='о'){

			$word2 = mb_substr($word1,0,-1,"UTF-8").'а';
			$word3 = mb_substr($word1,0,-1,"UTF-8").'ов';

		}elseif(mb_substr($word1,-1,1,"UTF-8")=='ц'){

			$word2 = mb_substr($word1,0,-1,"UTF-8").'ца';
			$word3 = mb_substr($word1,0,-1,"UTF-8").'цев';

		}elseif(mb_substr($word1,-3,3,"UTF-8")=='век'){

			$word2 = mb_substr($word1,0,-3,"UTF-8").'века';
			$word3 = mb_substr($word1,0,-3,"UTF-8").'веков';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='ек'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ка';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'ков';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='ёк'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ька';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'ьков';

		}elseif(in_array(mb_substr($word1,-1,1,"UTF-8"),array('б','в','г', 'д', 'ж', 'з', 'к', 'л', 'м', 'н', 'п', 'р', 'с', 'т', 'ф', 'х',   'ч', 'ш', 'щ' ))){

			$word2 = $word1.'а';
			$word3 = $word1.'ов';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='сь'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'си';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'сей';

		}elseif( mb_substr($word1,-2,2,"UTF-8")=='ть'){
			
			$word2 = mb_substr($word1,0,-2,"UTF-8").'ти';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'тей';

		}elseif(mb_substr($word1,-3,3,"UTF-8")=='ень'){

			$word2 = mb_substr($word1,0,-3,"UTF-8").'ня';
			$word3 = mb_substr($word1,0,-3,"UTF-8").'ней';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='нь'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ни';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'ней';

		}elseif(mb_substr($word1,-3,3,"UTF-8")=='брь'){

			$word2 = mb_substr($word1,0,-3,"UTF-8").'бря';
			$word3 = mb_substr($word1,0,-3,"UTF-8").'брей';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='рь'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ри';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'рей';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='ль'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ли';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'лей';

		}elseif(mb_substr($word1,-1,1,"UTF-8")=='ь'){

			$word2 = mb_substr($word1,0,-1,"UTF-8").'я';
			$word3 = mb_substr($word1,0,-1,"UTF-8").'ей';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='цы'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ц';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'ц';

		}elseif(mb_substr($word1,-1,1,"UTF-8")=='ы'){

			$word2 = mb_substr($word1,0,-1,"UTF-8").'ов';
			$word3 = mb_substr($word1,0,-1,"UTF-8").'ов';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='ия'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ии';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'ий';

		}elseif(mb_substr($word1,-3,3,"UTF-8")=='еля'){

			$word2 = mb_substr($word1,0,-3,"UTF-8").'ели';
			$word3 = mb_substr($word1,0,-3,"UTF-8").'ель';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='ля'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ли';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'лей';

		}elseif(mb_substr($word1,-1,1,"UTF-8")=='я'){

			$word2 = mb_substr($word1,0,-1,"UTF-8").'и';
			$word3 = mb_substr($word1,0,-1,"UTF-8").'ь';

		}elseif(mb_substr($word1,-3,3,"UTF-8")=='ька'){

			$word2 = mb_substr($word1,0,-3,"UTF-8").'ьки';
			$word3 = mb_substr($word1,0,-3,"UTF-8").'ек';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='ка'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ки';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'к';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='га'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ги';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'г';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='ча'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'чи';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'ч';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='жа'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'жи';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'ж';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='ша'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'ши';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'ш';

		}elseif(mb_substr($word1,-2,2,"UTF-8")=='ща'){

			$word2 = mb_substr($word1,0,-2,"UTF-8").'щи';
			$word3 = mb_substr($word1,0,-2,"UTF-8").'щ';

		}elseif(mb_substr($word1,-1,1,"UTF-8")=='а'){

			$word2 = mb_substr($word1,0,-1,"UTF-8").'ы';
			$word3 = mb_substr($word1,0,-1,"UTF-8").'';

		}elseif(mb_substr($word1,-1,1,"UTF-8")=='и'){

			$word2 = mb_substr($word1,0,-1,"UTF-8").'ов';
			$word3 = mb_substr($word1,0,-1,"UTF-8").'ов';

		}elseif(mb_substr($word1,-1,1,"UTF-8")=='е'){

			$word2 = mb_substr($word1,0,-1,"UTF-8").'я';
			$word3 = mb_substr($word1,0,-1,"UTF-8").'й';

		}
		$words_result=array($word1,$word2,$word3);

	}
	return $words_result[($number%100>4 && $number%100<20)? 2 : $checks[min($number%10, 5)]];
}


function userdate($date)
{
	return d()->Date($date)->user();
}
function userdate_mini($date)
{
	return d()->Date($date)->ru_user_mini();
}

function ml_userdate($date)
{
	return d()->Date($date)->ml_user();
}

/**
 * Функция, проверяющая валидность адреса электронной почты. Используется в валидаторах.
 *
 * @param $value Адрес электроннйо почты
 * @param $params Массив параметров валидатора (в данном случае пустой)
 * @return bool false, если адрес некорректен.
 */
function valid_email($value,$params)
{
	$value=strtolower($value);
	return ( 1 == preg_match(
		'/^[-a-z0-9\!\#\$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~]+(?:\.[-a-z0-9!' .
			'\#\$\%\&\'\*\+\/\=\?\^\_\`{|}~]+)*@(?:[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])?\.)*'.
			'(?:aero|arpa|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|[a-z][a-z])$/' ,$value));
}

function must_be_empty($value,$params)
{
	return $value=='';
}

function antispam($value,$params)
{
	$msg=strtolower($value);
	
	//однозначный список стопслов
	if ( strpos( $msg,'<a')!==false  ||  strpos( $msg,'лbных клиентов')!==false ||  strpos( $msg,'лbных клиентoв')!==false ||  strpos( $msg,'пoтенциалbных клиентов')!==false  ||  strpos( $msg,'базу данных потенциальных клиентов')!==false ||  strpos( $msg,'база данных потенциальных клиентов')!==false  ||  strpos( $msg,'база дaнныx пoтенциальных клиентов')!==false  ||  strpos( $msg,'базу дaнныx пoтенциальных клиентов')!==false  ||  strpos( $msg,'потeнциaлbных клиентoв')!==false  ||  strpos( $msg,'клиeнтская бaзa')!==false ||  strpos( $msg,'клиeнтская база')!==false || strpos( $msg,'клиeнтcкие базы')!==false || strpos( $msg,'ские бaзы')!==false || strpos( $msg,'ские базы')!==false ||strpos( $msg,'cкие бaзы')!==false  ||strpos( $msg,'cкие базы')!==false ||strpos( $msg,'скиe бaзы')!==false ||strpos( $msg,'скиe базы')!==false ||strpos( $msg,'cкиe бaзы')!==false ||strpos( $msg,'cкиe базы')!==false ||   strpos( $msg,'клиeнтские бaзы')!==false  ||  strpos( $msg,'клиeнтские базы')!==false || strpos( $msg,'ентсkи')!==false || strpos( $msg,'prodawez')!==false || strpos( $msg,'kлиент')!==false ||  strpos( $msg,'[url')!==false || strpos( $msg,'sex')!==false || strpos( $msg,'poker')!==false || strpos($msg,'casino')!==false     )  {
		return false;
	}
	
	//
	if(substr_count ( $msg,'http:/') >0 && !preg_match('#[а-яА-Я]#imu',$msg)){
		return false;
	}
	
	foreach(array('здравствуйте','купить','заказать','сколько стоит','ссылк','хотелось бы') as $stopword){
		if(strpos(mb_strtolower($msg,'UTF-8'),$stopword)!==false){
			return true;
		}
	}
	
	if(substr_count ( $msg,'http:/') >2){
		return false;
	}
	
	
	return true;
}

function br($str=false)
{
	if($str===false){
		print '<br />';
	}else{
		print nl2br($str);
	}
}

function container($param)
{
	$result='';
	$rows=explode("\n",$param);

	foreach($rows as $row){
		$row = trim($row);
	
		if($row=='[container]' || $row == ''){
			continue;
		}
		$row = explode("?",$row);
		if(count($row)!=2){
			continue;
		}
		$plugin_id = explode(";", $row[1]);
		if(d()->container[$row[0]]){
			d()->plugin_id = $plugin_id[0];
			d()->plugin_title = $plugin_id[1];
			$result .=  d()->call($row[0]);
		}
	}
	return $result;
}


//Всё, что связано с новым роутером
function route($url='/',$what=false,$to=false)
{
	static $anonymous_functions_count = 0;
	if($what==false && $url{0} != '^' && $url{0} != '/'){
		//route('/news/index', 'content', 'news#index');
		$what = 'content';
		$to = $url.'#';
		$url = '/'.$url.'/';
	}

	if($to == false){
		$to = $what;
		$what = 'content';
	}
	

	if(is_callable($to)){
		$anonymous_functions_count++;
		doitClass::$instance->callables['anonymous_callable'.$anonymous_functions_count]=$to;
		$to='anonymous_callable'.$anonymous_functions_count;
	//print serialize ($what);
	}
	doitClass::$instance->datapool['urls'][]=array($url,$what,$to);
}

function route_all()
{
	doitClass::$instance->is_using_route_all=true;
}

function transliterate_url($string)
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

	$str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
	$str = trim($str, "-");

	return $str;
    
}


function url_to_real($url) {
 $url = "/$url";
 if (substr($url, -6) === '/index') {
  $url = substr($url, 0, -5);
 }
 return $url;
}

function url_to_system($url) {
 if (substr($url, -1) === '/') {
  $url .= 'index';
 }
 if (substr($url, 0, 1) === '/') {
  $url = substr($url, 1);
 }
 return $url;
}

function times($array){
	if (is_array($array) && isset($array[0]) && isset($array[1])){
		$res = '';
		for ($i=1;$i<=(int)$array[0];$i++){
			$res .= $array[1];
		}
		return $res;
	}
	return '';
}


d()->singleton('view',function(){
	return new View;
});



function or_is_empty($arr=array()){
	$val  = $arr[0];
	$new_val = $arr[1];
	if(!$val){
		return $new_val;
	}
	return $val;
};