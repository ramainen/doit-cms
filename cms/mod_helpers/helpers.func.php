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
				$attr .= '  value= "'.  htmlspecialchars($_POST[$cfo][$params[0]]) .'" ';
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

