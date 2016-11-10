<?php

d()->as_title=function($value, $field, $object){
	if(substr($field,-3)=='_id'){
		$table = ActiveRecord::one_to_plural(substr($field,0,-3));
		$result = activerecord_factory_from_table($table)->find($value)->select('title');
		if($result->is_empty){
			return '';
		}
		return $result->title;
		
		
	}
	return '';
};

d()->as_object_title=function($value, $field, $object){
	if(substr($field,-3)=='_id'){
		return $object[substr($field,0,-3)]['title'];
	}
	return '';
};

d()->as_fast_title=function($value, $field, $object){
	if(substr($field,-3)=='_id'){
		return $object[substr($field,0,-3)]['title'];
	}
	return '';
};

d()->as_preview=function($value, $field, $object){
	if($value==''){
		return '';
	}
	return '<img src="'.d()->preview(array($value,'100','100')).'" alt="" />';
};


