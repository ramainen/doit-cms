<?php


class Scaffold extends UniversalHelper
{
	function create_field($table,$field)
	{
		if (substr($field,-3)=='_id') {
			d()->db->exec("ALTER TABLE `".$table."` ADD COLUMN `$field` int NULL");
		} elseif (substr($field,0,3)=='is_') {
			d()->db->exec("ALTER TABLE `".$table."` ADD COLUMN `$field` tinyint(4) NOT NULL DEFAULT 0");
		} elseif (substr($field,-3)=='_at') {
			d()->db->exec("ALTER TABLE `".$table."` ADD COLUMN `$field` datetime NULL");
		} else {
			d()->db->exec("ALTER TABLE `".$table."` ADD COLUMN `$field` text NULL, DEFAULT CHARACTER SET=utf8");
		}
	}
	
	function create_table($table,$one_element="")
	{
		if($one_element==''){
			$result = d()->db->exec("CREATE TABLE `".$table."` (
				`id`  int(11) NOT NULL AUTO_INCREMENT ,
				`url`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
				`text`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
				`title`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
				`template`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
				`type`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
				`multi_domain`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
				`sort`  int(11) NULL DEFAULT NULL ,
				PRIMARY KEY (`id`)
				)
				ENGINE=MyISAM
				DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
				;");
		}else{
			$result = d()->db->exec("CREATE TABLE `".$table."` (
				`id`  int(11) NOT NULL AUTO_INCREMENT ,
				`url`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
				`text`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
				`title`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
				`".$one_element."_id`  int(11) NULL DEFAULT NULL ,
				`template`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
				`type`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
				`multi_domain`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,				
				`sort`  int(11) NULL DEFAULT NULL ,
				PRIMARY KEY (`id`)
				)
				ENGINE=MyISAM
				DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
				;");		
		}
		return $result;
	}
	
}
