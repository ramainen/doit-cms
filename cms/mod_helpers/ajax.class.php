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
			$this->response.=  "$('.error').removeClass('error');\n";
			foreach($noticed_inputs as $key=>$input){
				$element_name = "'*[name=\"".$_POST['_element'].'['.$input.']'."\"]'";
				$this->response .=  '$('.$element_name.').parent().parent().addClass("error");'."\n";
			}
			
		}
		return $this->response;
	}
	function set_html($element,$html){
		$this->response .=  '$("'.$element.'").html('.json_encode($html).");\n";
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


