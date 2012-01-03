<?php

//helper input для отображения текстового поля
function input ($params=array())
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
	
	if(isset($params['checked'])) {
		$attr .= ' checked="'.$params['checked'].'" ';
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
		$attr .= ' name="'.$cfo.'['.$params[0].']" ';		
	}
	
	if(isset($params['type'])) {
		$attr .= ' type="'.$params['type'].'" ';
	} else {
		$attr .= ' type="text" '; 
	}
	
	if(isset($params['value'])) {
		$attr .= ' value="'.$params['value'].'" ';
	}else{
		if(isset($_POST['_action'])) {					
			if(isset($params['name'])) {
				$attr .= ' value= "'.  htmlspecialchars( $_POST[$params['name']]) .'" ';
			} else {
				$attr .= ' value= "'.  htmlspecialchars($_POST[$cfo][$params[0]]) .'" ';
			}
		} else {
			$attr .= ' value= "'.  htmlspecialchars(d($cfo)->{$params[0]}) .'" ';
		}
	}
	
	return '<input ' . $attr . '>';
}

function form ($params=array()) 
{
	$attr="";
	if (isset($params[1])) {
		d()->current_form_object = $params[1];
	} else {
		d()->current_form_object = 'data';
	}
	
	if(isset($params['action'])) {
		$attr .= ' action="'.$params['action'].'" ';
	}
	
	
	$result =  "<form method='POST' ".$attr.">";
	$result .= ' <input type="hidden" name="_element" value="' . d()->current_form_object .'" >';
	$result .= ' <input type="hidden" name="_action" value="'.$params[0].'" >';
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

function notice()
{
	if(d()->notice=='' || count(d()->notice)==0) {
		return '';
	}
	$str='';
	
	$str .= '<ul style="padding:15px;padding-left:25px;border:1px solid red;">';
	
	foreach(d()->notice as $value){
		$str .='<li>'.$value.'</li>';
	}
	
	$str .= '</ul>';
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

function preview($adress,$num=1 )
{
	if($adress==''){
		return '';
	}
	$ext=strtolower(strrchr($adress, '.'));
	if(!in_array($ext,array('.gif','.jpg','.png','.jpeg'))){
		return '';
	}
	return substr($adress, 0, strrpos($adress, "/") + 1) . ".thumbs/preview".$num."_" . substr($adress, strrpos($adress, "/") + 1);
}
function h($html)
{
	return htmlspecialchars($html);
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
	return ar::plural_to_one($plural);
}
//Получение единственного числа
function to_p($one)
{
	return ar::one_to_plural($one);
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
function declOfNum($number, $words)
{
	$checks = array (2, 0, 1, 1, 1, 2);
	return $words[($number%100>4 && $number%100<20)? 2 : $checks[min($number%10, 5)]];
}


function userdate($date)
{
	return d()->Date($date)->user();
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
	return ( 1 == preg_match(
		'/^[-a-z0-9\!\#\$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~]+(?:\.[-a-z0-9!' .
			'\#\$\%\&\'\*\+\/\=\?\^\_\`{|}~]+)*@(?:[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])?\.)*'.
			'(?:aero|arpa|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|[a-z][a-z])$/' ,$value));
}