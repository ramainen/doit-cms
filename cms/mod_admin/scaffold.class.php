<?php


class Scaffold extends UniversalHelper
{
	function create_field($table,$field){
		if (substr($field,-3)=='_id') {
			d()->db->exec("ALTER TABLE `".$table."` ADD COLUMN `$field` int NULL");
		} elseif (substr($field,0,3)=='is_') {
			d()->db->exec("ALTER TABLE `".$table."` ADD COLUMN `$field` tinyint(4) NOT NULL DEFAULT 0");
		} else {
			d()->db->exec("ALTER TABLE `".$table."` ADD COLUMN `$field` text NULL, DEFAULT CHARACTER SET=utf8");
		}
	}
}
