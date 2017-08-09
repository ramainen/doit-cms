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

d()->as_date_day=function($value, $field, $object){
	if($value==''){
		return '';
	}
	return d()->Date($value)->day;
};

d()->as_date_mm_yyyy=function($value, $field, $object){
	if($value==''){
		return '';
	}
	return d()->Date($value)->to_mm_yyyy();
};

d()->as_date_ru_month=function($value, $field, $object){
	if($value==''){
		return '';
	}
	return d()->Date($value)->ru_month;
};

d()->as_admin_check_mark = function($value, $field, $object) {
  if (iam()) {
    return $value
        ? '<span style="color:#3c3;font-weight:bold;font-size:1.5em;line-height:1em;vertical-align:middle;">☑️</span>'
        : '<span style="color:#c33;font-weight:bold;font-size:1.5em;line-height:1em;vertical-align:middle;">☒</span>';
  }
  return '';
};


