<?php

class Controller
{
	function obj_name()
	{
		$current_class =  get_class( $this);
		$current_obj = strtolower(substr($current_class,0,-10));
		return $current_obj;
	}
	function one_obj_name()
	{
		$current_class =  get_class( $this);
		$current_obj = to_o( strtolower(substr($current_class,0,-10)));
		return $current_obj;
	}
	function model_name()
	{
		$current_class =  get_class( $this);
		$current_obj = to_camel(to_o(strtolower(substr($current_class,0,-10))));
		return $current_obj;
	}
	
	
}


