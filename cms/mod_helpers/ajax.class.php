<?php

/*

	DoIt! CMS and VarVar framework
	Copyright (C) 2011 Fakhrutdinov Damir (aka Ainu)

	*      This program is free software; you can redistribute it and/or modify
	*      it under the terms of the GNU General Public License as published by
	*      the Free Software Foundation; either version 2 of the License, or
	*      (at your option) any later version.
	*
	*      This program is distributed in the hope that it will be useful,
	*      but WITHOUT ANY WARRANTY; without even the implied warranty of
	*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	*      GNU General Public License for more details.
	*
	*      You should have received a copy of the GNU General Public License
	*      along with this program; if not, write to the Free Software
	*      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
	*      MA 02110-1301, USA.
 */

/**
 * Класс для Аякса
 *
 */

class Ajax extends UniversalSingletoneHelper
{
	private $response='';
	function get_compiled_response()
	{
		
		if(isset(d()->datapool['inputs_with_errors']) && count(d()->datapool['inputs_with_errors'])!=0 && isset($_POST['_element'])){
			$noticed_inputs = array_values(d()->datapool['inputs_with_errors']);
			$this->response.=  "jQuery('.error, .has-error').removeClass('error').removeClass('has-error');\n";
			if ($_POST['_action'] == htmlspecialchars($_POST['_action'])){
				//$this->response.=  "var _tmp_form = jQuery('input[value=".$_POST['_action']."]').parents('form');\n";
				$this->response.=  "var _tmp_form = _current_form[0];\n";
			}else{
				$this->response.=  "var _tmp_form = jQuery(jQuery('form')[0]);\n";
			}
			
			
			$first_element=array();
			foreach($noticed_inputs as $key=>$input){
				if(isset($_POST['_is_simple_names']) && $_POST['_is_simple_names']=='1'){
					$element_name = "'*[name=\"".$input."\"]'";
				}else{
					$element_name = "'*[name=\"".$_POST['_element'].'['.$input.']'."\"]'";
				}
				$this->response .=  'jQuery('.$element_name.', _tmp_form).parent().parent().addClass("error").addClass("has-error");'."\n";
				if(isset($_POST['_is_simple_names']) && $_POST['_is_simple_names']=='1'){
					$first_element[] = "*[name=\"".$input."\"]" ;
				}else{
					$first_element[] = "*[name=\"".$_POST['_element'].'['.$input.']'."\"]" ;	
				}
				
			}
			if ($first_element != ''){
				$this->response .=  "jQuery(jQuery('".implode(', ',$first_element)."',  _tmp_form)[0]).focus();"."\n";
			}
		}
		return $this->response;
	}
	function set_html($element,$html){
		$this->response .=  'jQuery("'.$element.'").html('.json_encode($html).");\n";
	}

	function run_function($function_name){
		$this->response .=  $function_name."();\n";
	}
	
	function reload($adress=false){
		if($adress!==false){
			$this->response .=  "document.location.href='$adress';\n";
		} else {
			$this->response .=  "document.location.href=document.location.href;\n";
		}
	}
		
}


